-- CLICKET Phase 6: Ticket Generation, Validation, Printing, and Venue Check-In Operations
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: ticket issuance, ticket print history, validation scans, and venue check-in logs.
-- Dependencies: Phase 1 staff_accounts, Phase 2 seats, Phase 5 orders.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `checkin_logs`;
DROP TABLE IF EXISTS `ticket_print_logs`;
DROP TABLE IF EXISTS `tickets`;

CREATE TABLE `tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` VARCHAR(190) NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `seat_id` BIGINT UNSIGNED NOT NULL,
  `voucher_id` VARCHAR(190) DEFAULT NULL,
  `validation_code` VARCHAR(190) NOT NULL,
  `barcode_value` VARCHAR(190) NOT NULL,
  `status` ENUM('issued', 'active', 'used', 'cancelled', 'void') NOT NULL DEFAULT 'issued',
  `section` VARCHAR(120) NOT NULL,
  `row_label` VARCHAR(40) DEFAULT NULL,
  `seat_number` VARCHAR(40) DEFAULT NULL,
  `category` VARCHAR(120) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `issued_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `reissued_from_ticket_id` VARCHAR(190) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tickets_ticket_id` (`ticket_id`),
  UNIQUE KEY `uq_tickets_validation_code` (`validation_code`),
  KEY `idx_tickets_order_id` (`order_id`),
  KEY `idx_tickets_seat_id` (`seat_id`),
  KEY `idx_tickets_voucher_id` (`voucher_id`),
  KEY `idx_tickets_status` (`status`),
  KEY `idx_tickets_reissued_from_ticket_id` (`reissued_from_ticket_id`),
  CONSTRAINT `fk_tickets_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_seat`
    FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_reissued_from_ticket`
    FOREIGN KEY (`reissued_from_ticket_id`) REFERENCES `tickets` (`ticket_id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ticket_print_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `printed_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `print_context` VARCHAR(80) NOT NULL DEFAULT 'customer',
  `printed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_print_logs_ticket_id` (`ticket_id`),
  KEY `idx_ticket_print_logs_order_id` (`order_id`),
  KEY `idx_ticket_print_logs_printed_by_staff_id` (`printed_by_staff_id`),
  KEY `idx_ticket_print_logs_print_context` (`print_context`),
  CONSTRAINT `fk_ticket_print_logs_ticket`
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_ticket_print_logs_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_ticket_print_logs_printed_by_staff`
    FOREIGN KEY (`printed_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `checkin_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED DEFAULT NULL,
  `validation_code` VARCHAR(190) NOT NULL,
  `scanned_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `gate_name` VARCHAR(120) DEFAULT NULL,
  `scan_result` ENUM('valid', 'already_used', 'invalid', 'blocked') NOT NULL,
  `message` VARCHAR(255) DEFAULT NULL,
  `scanned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_checkin_logs_ticket_id` (`ticket_id`),
  KEY `idx_checkin_logs_validation_code` (`validation_code`),
  KEY `idx_checkin_logs_scanned_by_staff_id` (`scanned_by_staff_id`),
  KEY `idx_checkin_logs_scan_result` (`scan_result`),
  KEY `idx_checkin_logs_scanned_at` (`scanned_at`),
  CONSTRAINT `fk_checkin_logs_ticket`
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT `fk_checkin_logs_scanned_by_staff`
    FOREIGN KEY (`scanned_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
