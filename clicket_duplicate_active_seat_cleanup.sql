-- CLICKET Duplicate Active Seat Cleanup
-- Purpose: resolve duplicate active order_seats before enabling
-- uq_order_seats_active_event_performance_seat.
-- No orders, payments, or tickets are deleted.

USE `clicket`;

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';
SET NAMES utf8mb4;

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS tmp_clicket_duplicate_groups;
DROP TEMPORARY TABLE IF EXISTS tmp_clicket_duplicate_candidates;
DROP TEMPORARY TABLE IF EXISTS tmp_clicket_duplicate_winners;
DROP TEMPORARY TABLE IF EXISTS tmp_clicket_duplicate_losers;

CREATE TEMPORARY TABLE tmp_clicket_duplicate_groups AS
SELECT event_id, performance_id, seat_id, COUNT(*) AS active_count
FROM order_seats
WHERE active_reservation_key = 'active'
GROUP BY event_id, performance_id, seat_id
HAVING COUNT(*) > 1;

CREATE TEMPORARY TABLE tmp_clicket_duplicate_candidates AS
SELECT os.id AS order_seat_id,
       os.event_id,
       os.performance_id,
       os.seat_id,
       os.seat_code,
       os.order_id AS order_pk,
       o.order_id,
       o.payment_status,
       o.order_status,
       os.reservation_status,
       o.buyer_name,
       o.buyer_email,
       o.booked_at,
       CASE
         WHEN o.payment_status = 'approved' AND o.order_status = 'completed' THEN 500
         WHEN o.payment_status = 'approved' AND o.order_status = 'approved' THEN 400
         WHEN o.payment_status = 'under_review' AND o.order_status = 'pending' THEN 300
         WHEN o.payment_status = 'pending' AND o.order_status = 'pending' THEN 200
         ELSE 100
       END AS priority_score
FROM order_seats os
INNER JOIN orders o ON o.id = os.order_id
INNER JOIN tmp_clicket_duplicate_groups dg
  ON dg.event_id = os.event_id
 AND dg.performance_id = os.performance_id
 AND dg.seat_id = os.seat_id
WHERE os.active_reservation_key = 'active';

CREATE TEMPORARY TABLE tmp_clicket_duplicate_winners AS
SELECT c.*
FROM tmp_clicket_duplicate_candidates c
WHERE NOT EXISTS (
  SELECT 1
  FROM tmp_clicket_duplicate_candidates other
  WHERE other.event_id = c.event_id
    AND other.performance_id = c.performance_id
    AND other.seat_id = c.seat_id
    AND (
      other.priority_score > c.priority_score
      OR (other.priority_score = c.priority_score AND other.booked_at > c.booked_at)
      OR (other.priority_score = c.priority_score AND other.booked_at = c.booked_at AND other.order_seat_id > c.order_seat_id)
    )
);

CREATE TEMPORARY TABLE tmp_clicket_duplicate_losers AS
SELECT c.*
FROM tmp_clicket_duplicate_candidates c
LEFT JOIN tmp_clicket_duplicate_winners w
  ON w.order_seat_id = c.order_seat_id
WHERE w.order_seat_id IS NULL;

INSERT INTO audit_logs
  (actor_staff_id, actor_role, action_type, entity_type, entity_id, old_value, new_value, ip_address)
SELECT NULL,
       'system',
       'duplicate_active_seat_cleanup',
       'order_seats',
       l.order_seat_id,
       CONCAT(
         'order_id=', l.order_id,
         '; event_id=', l.event_id,
         '; performance_id=', l.performance_id,
         '; seat_id=', l.seat_id,
         '; seat_code=', l.seat_code,
         '; payment_status=', l.payment_status,
         '; order_status=', l.order_status,
         '; reservation_status=', l.reservation_status,
         '; active_reservation_key=active'
       ),
       'reservation_status=released; active_reservation_key=NULL',
       '127.0.0.1'
FROM tmp_clicket_duplicate_losers l;

INSERT INTO audit_logs
  (actor_staff_id, actor_role, action_type, entity_type, entity_id, old_value, new_value, ip_address)
SELECT NULL,
       'system',
       'duplicate_active_ticket_cancelled',
       'tickets',
       t.id,
       CONCAT(
         'ticket_id=', t.ticket_id,
         '; order_id=', l.order_id,
         '; event_id=', l.event_id,
         '; performance_id=', l.performance_id,
         '; seat_id=', l.seat_id,
         '; old_status=', t.status
       ),
       'status=cancelled; duplicate active seat reservation released',
       '127.0.0.1'
FROM tickets t
INNER JOIN tmp_clicket_duplicate_losers l
  ON l.order_pk = t.order_id
 AND l.seat_id = t.seat_id;

UPDATE tickets t
INNER JOIN tmp_clicket_duplicate_losers l
  ON l.order_pk = t.order_id
 AND l.seat_id = t.seat_id
SET t.status = 'cancelled';

UPDATE order_seats os
INNER JOIN tmp_clicket_duplicate_losers l
  ON l.order_seat_id = os.id
SET os.reservation_status = 'released',
    os.active_reservation_key = NULL;

UPDATE seats s
INNER JOIN tmp_clicket_duplicate_winners w ON w.seat_id = s.id
SET s.status = w.reservation_status;

COMMIT;

SELECT 'duplicate cleanup complete' AS migration_note;

SELECT os.event_id, os.performance_id, os.seat_id, COUNT(*) AS active_count
FROM order_seats os
WHERE os.active_reservation_key = 'active'
GROUP BY os.event_id, os.performance_id, os.seat_id
HAVING COUNT(*) > 1;
