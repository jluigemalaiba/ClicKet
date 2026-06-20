-- CLICKET Reservation Engine Hardening Migration
-- Purpose: protect pending payment-review orders, scope order seats by event/performance,
-- and add an active-seat uniqueness guard without deleting payment/order history.
-- Run after clicket_final_database.sql has been imported.

USE `clicket`;

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';
SET NAMES utf8mb4;

SET @schema_name := DATABASE();

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND COLUMN_NAME = 'event_id'
  ),
  'SELECT "order_seats.event_id already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD COLUMN `event_id` BIGINT UNSIGNED NULL AFTER `order_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND COLUMN_NAME = 'performance_id'
  ),
  'SELECT "order_seats.performance_id already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD COLUMN `performance_id` BIGINT UNSIGNED NULL AFTER `event_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND COLUMN_NAME = 'reservation_status'
  ),
  'SELECT "order_seats.reservation_status already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD COLUMN `reservation_status` ENUM(''held'', ''sold'', ''released'') NOT NULL DEFAULT ''held'' AFTER `ticket_code`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND COLUMN_NAME = 'active_reservation_key'
  ),
  'SELECT "order_seats.active_reservation_key already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD COLUMN `active_reservation_key` VARCHAR(12) DEFAULT ''active'' AFTER `reservation_status`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `order_seats` os
INNER JOIN `orders` o ON o.id = os.order_id
SET os.event_id = o.event_id,
    os.performance_id = o.performance_id
WHERE os.event_id IS NULL OR os.performance_id IS NULL;

UPDATE `order_seats` os
INNER JOIN `orders` o ON o.id = os.order_id
SET os.reservation_status = CASE
      WHEN o.payment_status = 'approved' AND o.order_status IN ('approved', 'completed') THEN 'sold'
      WHEN o.payment_status IN ('pending', 'under_review') AND o.order_status = 'pending' THEN 'held'
      ELSE 'released'
    END,
    os.active_reservation_key = CASE
      WHEN (o.payment_status = 'approved' AND o.order_status IN ('approved', 'completed'))
        OR (o.payment_status IN ('pending', 'under_review') AND o.order_status = 'pending')
      THEN 'active'
      ELSE NULL
    END;

ALTER TABLE `order_seats`
  MODIFY `event_id` BIGINT UNSIGNED NOT NULL,
  MODIFY `performance_id` BIGINT UNSIGNED NOT NULL;

-- If this query returns rows, resolve duplicate active reservations before adding the unique key.
SELECT os.event_id, os.performance_id, os.seat_id, COUNT(*) AS active_duplicate_count
FROM `order_seats` os
WHERE os.active_reservation_key = 'active'
GROUP BY os.event_id, os.performance_id, os.seat_id
HAVING COUNT(*) > 1;

SELECT COUNT(*) INTO @active_duplicate_count
FROM (
  SELECT os.event_id, os.performance_id, os.seat_id
  FROM `order_seats` os
  WHERE os.active_reservation_key = 'active'
  GROUP BY os.event_id, os.performance_id, os.seat_id
  HAVING COUNT(*) > 1
) duplicate_active_order_seats;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND INDEX_NAME = 'idx_order_seats_event_id'
  ),
  'SELECT "idx_order_seats_event_id already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD KEY `idx_order_seats_event_id` (`event_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND INDEX_NAME = 'idx_order_seats_performance_id'
  ),
  'SELECT "idx_order_seats_performance_id already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD KEY `idx_order_seats_performance_id` (`performance_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND INDEX_NAME = 'idx_order_seats_reservation_status'
  ),
  'SELECT "idx_order_seats_reservation_status already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD KEY `idx_order_seats_reservation_status` (`reservation_status`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND INDEX_NAME = 'uq_order_seats_active_event_performance_seat'
  ),
  'SELECT "uq_order_seats_active_event_performance_seat already exists" AS migration_note',
  IF(
    @active_duplicate_count = 0,
    'ALTER TABLE `order_seats` ADD UNIQUE KEY `uq_order_seats_active_event_performance_seat` (`event_id`, `performance_id`, `seat_id`, `active_reservation_key`)',
    'SELECT "Skipped unique active seat guard because existing active duplicate order seats were found; resolve duplicates, then add uq_order_seats_active_event_performance_seat manually." AS migration_note'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats'
      AND INDEX_NAME IN ('uq_order_seats_active_event_performance_seat', 'idx_order_seats_active_event_performance_seat')
  ),
  'SELECT "active event/performance/seat index already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD KEY `idx_order_seats_active_event_performance_seat` (`event_id`, `performance_id`, `seat_id`, `active_reservation_key`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND CONSTRAINT_NAME = 'fk_order_seats_event'
  ),
  'SELECT "fk_order_seats_event already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD CONSTRAINT `fk_order_seats_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_seats' AND CONSTRAINT_NAME = 'fk_order_seats_performance'
  ),
  'SELECT "fk_order_seats_performance already exists" AS migration_note',
  'ALTER TABLE `order_seats` ADD CONSTRAINT `fk_order_seats_performance` FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
