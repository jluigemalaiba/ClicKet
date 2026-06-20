-- CLICKET Phase 5: Order Processing and Payment Management
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: checkout orders, purchased seat snapshots, payment records, and payment proof uploads.
-- Dependencies: Phase 1 users and staff_accounts, Phase 2 seats, Phase 3 events and event_performances.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `payment_proofs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_seats`;
DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` VARCHAR(190) NOT NULL,
  `reference` VARCHAR(190) DEFAULT NULL,
  `payment_reference` VARCHAR(190) DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `buyer_name` VARCHAR(120) NOT NULL,
  `buyer_email` VARCHAR(190) NOT NULL,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_id` BIGINT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `service_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `non_transferable` TINYINT(1) NOT NULL DEFAULT 1,
  `payment_status` ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `order_status` ENUM('pending', 'approved', 'rejected', 'completed', 'archived') NOT NULL DEFAULT 'pending',
  `booked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `rejected_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `rejected_at` TIMESTAMP NULL DEFAULT NULL,
  `archived_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_order_id` (`order_id`),
  KEY `idx_orders_reference` (`reference`),
  KEY `idx_orders_payment_reference` (`payment_reference`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_event_id` (`event_id`),
  KEY `idx_orders_performance_id` (`performance_id`),
  KEY `idx_orders_approved_by_staff_id` (`approved_by_staff_id`),
  KEY `idx_orders_rejected_by_staff_id` (`rejected_by_staff_id`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_order_status` (`order_status`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_performance`
    FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_approved_by_staff`
    FOREIGN KEY (`approved_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT `fk_orders_rejected_by_staff`
    FOREIGN KEY (`rejected_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_seats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_id` BIGINT UNSIGNED NOT NULL,
  `seat_id` BIGINT UNSIGNED NOT NULL,
  `seat_code` VARCHAR(120) NOT NULL,
  `section` VARCHAR(120) NOT NULL,
  `row_label` VARCHAR(40) DEFAULT NULL,
  `seat_number` VARCHAR(40) DEFAULT NULL,
  `category` VARCHAR(120) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `ticket_code` VARCHAR(190) DEFAULT NULL,
  `reservation_status` ENUM('held', 'sold', 'released') NOT NULL DEFAULT 'held',
  `active_reservation_key` VARCHAR(12) DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_seats_order_seat` (`order_id`, `seat_id`),
  UNIQUE KEY `uq_order_seats_active_event_performance_seat` (`event_id`, `performance_id`, `seat_id`, `active_reservation_key`),
  UNIQUE KEY `uq_order_seats_ticket_code` (`ticket_code`),
  KEY `idx_order_seats_order_id` (`order_id`),
  KEY `idx_order_seats_event_id` (`event_id`),
  KEY `idx_order_seats_performance_id` (`performance_id`),
  KEY `idx_order_seats_seat_id` (`seat_id`),
  KEY `idx_order_seats_reservation_status` (`reservation_status`),
  CONSTRAINT `fk_order_seats_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_order_seats_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_order_seats_performance`
    FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_order_seats_seat`
    FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `payment_reference` VARCHAR(190) NOT NULL,
  `method` VARCHAR(80) NOT NULL,
  `method_label` VARCHAR(120) NOT NULL,
  `payment_account` VARCHAR(190) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_order_id` (`order_id`),
  KEY `idx_payments_payment_reference` (`payment_reference`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_reviewed_by_staff_id` (`reviewed_by_staff_id`),
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_payments_reviewed_by_staff`
    FOREIGN KEY (`reviewed_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_proofs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(120) DEFAULT NULL,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `review_status` ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `review_note` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payment_proofs_payment_id` (`payment_id`),
  KEY `idx_payment_proofs_order_id` (`order_id`),
  KEY `idx_payment_proofs_review_status` (`review_status`),
  CONSTRAINT `fk_payment_proofs_payment`
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_payment_proofs_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
