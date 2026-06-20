<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function clicketInventorySoldOrderCondition(string $alias = 'o'): string {
    return $alias . '.payment_status = "approved" AND ' . $alias . '.order_status IN ("approved", "completed")';
}

function clicketInventoryHeldOrderCondition(string $alias = 'o'): string {
    return $alias . '.payment_status IN ("pending", "under_review") AND ' . $alias . '.order_status = "pending"';
}

function clicketInventoryReservedOrderCondition(string $alias = 'o'): string {
    return '((' . clicketInventorySoldOrderCondition($alias) . ') OR (' . clicketInventoryHeldOrderCondition($alias) . '))';
}

function clicketInventoryScope(string $column, ?int $id): string {
    return $id !== null && $id > 0 ? ' AND ' . $column . ' = ' . (int) $id : '';
}

function clicketInventorySyncAll(?int $eventId = null, ?int $performanceId = null): void {
    $eventScope = clicketInventoryScope('e.id', $eventId);
    $performanceScope = clicketInventoryScope('ep.id', $performanceId);
    $esiEventScope = clicketInventoryScope('esi.event_id', $eventId);
    $esiPerformanceScope = clicketInventoryScope('esi.performance_id', $performanceId);

    clicketDbExecute(
        'INSERT INTO event_section_inventory
           (event_id, performance_id, venue_section_id, capacity, sold_count, held_count, blocked_count, available_count, status)
         SELECT e.id,
                ep.id,
                vs.id,
                GREATEST(COALESCE(vs.capacity, 0), COUNT(s.id)) AS capacity,
                0,
                0,
                0,
                GREATEST(COALESCE(vs.capacity, 0), COUNT(s.id)),
                "available"
         FROM events e
         INNER JOIN event_performances ep ON ep.event_id = e.id
         INNER JOIN venue_sections vs ON vs.venue_layout_id = e.venue_layout_id
         LEFT JOIN seats s ON s.venue_section_id = vs.id
         WHERE 1 = 1' . $eventScope . $performanceScope . '
         GROUP BY e.id, ep.id, vs.id, vs.capacity
         ON DUPLICATE KEY UPDATE
           capacity = VALUES(capacity)'
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         SET sold_count = 0,
             held_count = 0,
             blocked_count = 0,
             available_count = capacity,
             status = "available"
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         INNER JOIN (
           SELECT o.event_id, o.performance_id, s.venue_section_id, COUNT(DISTINCT os.seat_id) AS sold_count
           FROM orders o
           INNER JOIN order_seats os ON os.order_id = o.id
           INNER JOIN seats s ON s.id = os.seat_id
           WHERE ' . clicketInventorySoldOrderCondition('o') . '
             AND os.active_reservation_key = "active"
             AND os.reservation_status = "sold"
           GROUP BY o.event_id, o.performance_id, s.venue_section_id
         ) sold ON sold.event_id = esi.event_id
                AND sold.performance_id = esi.performance_id
                AND sold.venue_section_id = esi.venue_section_id
         SET esi.sold_count = sold.sold_count
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         INNER JOIN (
           SELECT event_id, performance_id, venue_section_id, SUM(held_count) AS held_count
           FROM (
             SELECT h.event_id, h.performance_id, s.venue_section_id, COUNT(DISTINCT hi.seat_id) AS held_count
             FROM seat_holds h
             INNER JOIN seat_hold_items hi ON hi.seat_hold_id = h.id
             INNER JOIN seats s ON s.id = hi.seat_id
             WHERE h.status = "active" AND h.expires_at > UTC_TIMESTAMP()
             GROUP BY h.event_id, h.performance_id, s.venue_section_id
             UNION ALL
             SELECT o.event_id, o.performance_id, s.venue_section_id, COUNT(DISTINCT os.seat_id) AS held_count
             FROM orders o
             INNER JOIN order_seats os ON os.order_id = o.id
             INNER JOIN seats s ON s.id = os.seat_id
             WHERE ' . clicketInventoryHeldOrderCondition('o') . '
               AND os.active_reservation_key = "active"
               AND os.reservation_status = "held"
             GROUP BY o.event_id, o.performance_id, s.venue_section_id
           ) held_source
           GROUP BY event_id, performance_id, venue_section_id
         ) held ON held.event_id = esi.event_id
                AND held.performance_id = esi.performance_id
                AND held.venue_section_id = esi.venue_section_id
         SET esi.held_count = held.held_count
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         INNER JOIN (
           SELECT sb.event_id, sb.performance_id, s.venue_section_id, COUNT(DISTINCT sb.seat_id) AS blocked_count
           FROM seat_blocks sb
           INNER JOIN seats s ON s.id = sb.seat_id
           WHERE sb.status = "active"
           GROUP BY sb.event_id, sb.performance_id, s.venue_section_id
         ) blocked ON blocked.event_id = esi.event_id
                   AND blocked.performance_id = esi.performance_id
                   AND blocked.venue_section_id = esi.venue_section_id
         SET esi.blocked_count = GREATEST(esi.blocked_count, blocked.blocked_count)
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         INNER JOIN venue_sections vs ON vs.id = esi.venue_section_id
         INNER JOIN tier_blocks tb ON tb.event_id = esi.event_id
                                  AND tb.tier_id = vs.tier_id
                                  AND tb.status = "active"
         SET esi.blocked_count = GREATEST(
             esi.blocked_count,
             GREATEST(0, esi.capacity - esi.sold_count - esi.held_count)
         )
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    clicketDbExecute(
        'UPDATE event_section_inventory esi
         SET available_count = GREATEST(0, capacity - sold_count - held_count - blocked_count),
             status = CASE
               WHEN blocked_count >= GREATEST(1, capacity - sold_count - held_count) THEN "blocked"
               WHEN capacity > 0 AND sold_count + held_count + blocked_count >= capacity THEN "sold_out"
               ELSE "available"
             END
         WHERE 1 = 1' . $esiEventScope . $esiPerformanceScope
    );

    $tierEventScope = clicketInventoryScope('e.id', $eventId);
    clicketDbExecute(
        'INSERT INTO event_tier_settings
           (event_id, tier_id, price, capacity, sold_count, held_count, available_count, status, blocked_reason)
         SELECT e.id,
                vt.id,
                COALESCE(e.base_price, 0),
                COALESCE(SUM(esi.capacity), 0),
                COALESCE(SUM(esi.sold_count), 0),
                COALESCE(SUM(esi.held_count), 0),
                COALESCE(SUM(esi.available_count), 0),
                CASE WHEN MAX(CASE WHEN tb.id IS NULL THEN 0 ELSE 1 END) = 1 THEN "blocked" ELSE "active" END,
                CASE WHEN MAX(CASE WHEN tb.id IS NULL THEN 0 ELSE 1 END) = 1 THEN MAX(tb.reason) ELSE NULL END
         FROM events e
         INNER JOIN venue_tiers vt ON vt.venue_layout_id = e.venue_layout_id
         LEFT JOIN venue_sections vs ON vs.tier_id = vt.id
         LEFT JOIN event_section_inventory esi ON esi.event_id = e.id AND esi.venue_section_id = vs.id
         LEFT JOIN tier_blocks tb ON tb.event_id = e.id AND tb.tier_id = vt.id AND tb.status = "active"
         WHERE 1 = 1' . $tierEventScope . '
         GROUP BY e.id, vt.id, e.base_price
         ON DUPLICATE KEY UPDATE
           price = CASE
             WHEN event_tier_settings.price > 0 THEN event_tier_settings.price
             ELSE VALUES(price)
           END,
           capacity = VALUES(capacity),
           sold_count = VALUES(sold_count),
           held_count = VALUES(held_count),
           available_count = VALUES(available_count),
           status = VALUES(status),
           blocked_reason = VALUES(blocked_reason)'
    );
}

function clicketInventorySyncEventPerformance(int $eventId, int $performanceId): void {
    clicketInventorySyncAll($eventId, null);
}

function clicketInventoryUnavailableSeatIds(int $eventId, int $performanceId, array $seatIds, ?string $ownHoldToken = null, ?int $ignoreOrderPk = null, bool $lock = false): array {
    $seatIds = array_values(array_unique(array_filter(array_map('intval', $seatIds))));
    if (!$seatIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $unavailable = [];

    $orderParams = array_merge([$eventId, $performanceId], $seatIds);
    $ignoreOrderSql = '';
    if ($ignoreOrderPk !== null && $ignoreOrderPk > 0) {
        $ignoreOrderSql = ' AND o.id <> ?';
        $orderParams[] = $ignoreOrderPk;
    }
    $orderRows = clicketDbExecute(
        'SELECT os.seat_id
         FROM orders o
         INNER JOIN order_seats os ON os.order_id = o.id
         WHERE o.event_id = ?
           AND o.performance_id = ?
           AND os.active_reservation_key = "active"
           AND os.seat_id IN (' . $placeholders . ')' . $ignoreOrderSql .
           ($lock ? ' FOR UPDATE' : ''),
        $orderParams
    )->fetchAll();
    foreach ($orderRows as $row) {
        $unavailable[(int) $row['seat_id']] = true;
    }

    $holdParams = array_merge([$eventId, $performanceId], $seatIds);
    $holdTokenSql = '';
    if ($ownHoldToken !== null && $ownHoldToken !== '') {
        $holdTokenSql = ' AND h.token <> ?';
        $holdParams[] = $ownHoldToken;
    }
    $holdRows = clicketDbExecute(
        'SELECT hi.seat_id
         FROM seat_holds h
         INNER JOIN seat_hold_items hi ON hi.seat_hold_id = h.id
         WHERE h.event_id = ?
           AND h.performance_id = ?
           AND h.status = "active"
           AND h.expires_at > UTC_TIMESTAMP()
           AND hi.seat_id IN (' . $placeholders . ')' . $holdTokenSql .
           ($lock ? ' FOR UPDATE' : ''),
        $holdParams
    )->fetchAll();
    foreach ($holdRows as $row) {
        $unavailable[(int) $row['seat_id']] = true;
    }

    $blockParams = array_merge([$eventId, $performanceId], $seatIds, [$eventId], $seatIds);
    $blockRows = clicketDbExecute(
        'SELECT sb.seat_id
         FROM seat_blocks sb
         WHERE sb.event_id = ?
           AND sb.performance_id = ?
           AND sb.status = "active"
           AND sb.seat_id IN (' . $placeholders . ')
         UNION
         SELECT s.id AS seat_id
         FROM tier_blocks tb
         INNER JOIN venue_sections vs ON vs.tier_id = tb.tier_id
         INNER JOIN seats s ON s.venue_section_id = vs.id
         WHERE tb.event_id = ?
           AND tb.status = "active"
           AND s.id IN (' . $placeholders . ')',
        $blockParams
    )->fetchAll();
    foreach ($blockRows as $row) {
        $unavailable[(int) $row['seat_id']] = true;
    }

    return array_keys($unavailable);
}

function clicketInventoryBlockedSeatCodes(string $eventKey, int $performance): array {
    $event = clicketDbEventByKey($eventKey);
    $performanceRow = clicketDbPerformanceByIndex($eventKey, max(0, min(3, $performance)));
    if (!$event || !$performanceRow) {
        return [];
    }

    $rows = clicketDbExecute(
        'SELECT DISTINCT s.seat_code
         FROM seat_blocks sb
         INNER JOIN seats s ON s.id = sb.seat_id
         WHERE sb.event_id = ?
           AND sb.performance_id = ?
           AND sb.status = "active"
         UNION
         SELECT DISTINCT s.seat_code
         FROM tier_blocks tb
         INNER JOIN venue_sections vs ON vs.tier_id = tb.tier_id
         INNER JOIN seats s ON s.venue_section_id = vs.id
         WHERE tb.event_id = ?
           AND tb.status = "active"
         ORDER BY seat_code',
        [
            (int) $event['id'],
            (int) $performanceRow['id'],
            (int) $event['id'],
        ]
    )->fetchAll();

    return array_values(array_map(static fn (array $row): string => (string) $row['seat_code'], $rows));
}

function clicketInventorySectionRows(?array $eventKeys = null): array {
    clicketInventorySyncAll();

    $params = [];
    $where = '';
    if ($eventKeys !== null) {
        $eventKeys = array_values(array_filter(array_map('strval', $eventKeys)));
        if (!$eventKeys) {
            return [];
        }
        $where = ' WHERE e.event_key IN (' . implode(',', array_fill(0, count($eventKeys), '?')) . ')';
        $params = $eventKeys;
    }

    $rows = clicketDbExecute(
        'SELECT esi.*, e.event_key, e.title AS event_title,
                v.name AS venue_name, vl.variant AS layout_variant,
                vs.label AS section_label, vt.name AS tier_name,
                ep.performance_date, ep.performance_time
         FROM event_section_inventory esi
         INNER JOIN events e ON e.id = esi.event_id
         INNER JOIN event_performances ep ON ep.id = esi.performance_id
         INNER JOIN venue_sections vs ON vs.id = esi.venue_section_id
         INNER JOIN venue_tiers vt ON vt.id = vs.tier_id
         INNER JOIN venue_layouts vl ON vl.id = e.venue_layout_id
         INNER JOIN venues v ON v.id = e.venue_id' . $where . '
         ORDER BY e.title, ep.performance_date, ep.performance_time, v.name, vs.label',
        $params
    )->fetchAll();

    return array_map(static function (array $row): array {
        return [
            'event' => (string) $row['event_key'],
            'event_title' => (string) $row['event_title'],
            'venue' => (string) $row['venue_name'],
            'variant' => (string) ($row['layout_variant'] ?? ''),
            'section' => (string) $row['section_label'],
            'tier' => (string) $row['tier_name'],
            'performance' => trim(clicketDbDisplayDate((string) $row['performance_date']) . ' ' . clicketDbDisplayTime((string) $row['performance_time'])),
            'capacity' => (int) $row['capacity'],
            'available' => (int) $row['available_count'],
            'sold' => (int) $row['sold_count'],
            'held' => (int) $row['held_count'],
            'blocked' => (int) $row['blocked_count'],
            'accessible' => 0,
            'complimentary' => 0,
            'status' => (string) $row['status'],
        ];
    }, $rows);
}

function clicketInventoryTierRows(?array $eventKeys = null): array {
    clicketInventorySyncAll();

    $params = [];
    $where = '';
    if ($eventKeys !== null) {
        $eventKeys = array_values(array_filter(array_map('strval', $eventKeys)));
        if (!$eventKeys) {
            return [];
        }
        $where = ' WHERE e.event_key IN (' . implode(',', array_fill(0, count($eventKeys), '?')) . ')';
        $params = $eventKeys;
    }

    $rows = clicketDbExecute(
        'SELECT ets.*, e.event_key, e.title AS event_title,
                vt.name AS tier_name, v.name AS venue_name, vl.variant AS layout_variant
         FROM event_tier_settings ets
         INNER JOIN events e ON e.id = ets.event_id
         INNER JOIN venue_tiers vt ON vt.id = ets.tier_id
         INNER JOIN venue_layouts vl ON vl.id = vt.venue_layout_id
         INNER JOIN venues v ON v.id = vl.venue_id' . $where . '
         ORDER BY e.title, vt.sort_order, vt.name',
        $params
    )->fetchAll();

    return array_map(static function (array $row): array {
        return [
            'event' => (string) $row['event_key'],
            'event_title' => (string) $row['event_title'],
            'venue' => (string) $row['venue_name'],
            'variant' => (string) ($row['layout_variant'] ?? ''),
            'tier' => (string) $row['tier_name'],
            'capacity' => (int) $row['capacity'],
            'sold' => (int) $row['sold_count'],
            'held' => (int) $row['held_count'],
            'available' => (int) $row['available_count'],
            'status' => (string) $row['status'],
            'revenue' => (int) round((float) $row['price'] * (int) $row['sold_count']),
        ];
    }, $rows);
}

function clicketInventorySummary(?array $eventKeys = null): array {
    $sections = clicketInventorySectionRows($eventKeys);

    return [
        'capacity' => array_sum(array_column($sections, 'capacity')),
        'available' => array_sum(array_column($sections, 'available')),
        'sold' => array_sum(array_column($sections, 'sold')),
        'held' => array_sum(array_column($sections, 'held')),
        'blocked' => array_sum(array_column($sections, 'blocked')),
        'accessible' => array_sum(array_column($sections, 'accessible')),
        'complimentary' => array_sum(array_column($sections, 'complimentary')),
    ];
}
