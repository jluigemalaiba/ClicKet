-- CLICKET Phase 4: Event Inventory and Seat Reservation Management
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: event-level inventory, performance section inventory, and temporary seat holds.
-- Dependencies: Phase 1 users and staff_accounts, Phase 2 venue_tiers, venue_sections, and seats, Phase 3 events and event_performances.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `seat_hold_items`;
DROP TABLE IF EXISTS `seat_holds`;
DROP TABLE IF EXISTS `event_section_inventory`;
DROP TABLE IF EXISTS `event_tier_settings`;

CREATE TABLE `event_tier_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `tier_id` BIGINT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 0,
  `sold_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `held_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `available_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive', 'blocked') NOT NULL DEFAULT 'active',
  `blocked_reason` VARCHAR(255) DEFAULT NULL,
  `updated_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_tier_settings_event_tier` (`event_id`, `tier_id`),
  KEY `idx_event_tier_settings_event_id` (`event_id`),
  KEY `idx_event_tier_settings_tier_id` (`tier_id`),
  KEY `idx_event_tier_settings_updated_by_staff_id` (`updated_by_staff_id`),
  KEY `idx_event_tier_settings_status` (`status`),
  CONSTRAINT `fk_event_tier_settings_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_event_tier_settings_tier`
    FOREIGN KEY (`tier_id`) REFERENCES `venue_tiers` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_event_tier_settings_updated_by_staff`
    FOREIGN KEY (`updated_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_section_inventory` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_id` BIGINT UNSIGNED NOT NULL,
  `venue_section_id` BIGINT UNSIGNED NOT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 0,
  `sold_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `held_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `blocked_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `available_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('available', 'sold_out', 'blocked', 'inactive') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_section_inventory_event_performance_section` (`event_id`, `performance_id`, `venue_section_id`),
  KEY `idx_event_section_inventory_event_id` (`event_id`),
  KEY `idx_event_section_inventory_performance_id` (`performance_id`),
  KEY `idx_event_section_inventory_venue_section_id` (`venue_section_id`),
  KEY `idx_event_section_inventory_status` (`status`),
  CONSTRAINT `fk_event_section_inventory_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_event_section_inventory_performance`
    FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_event_section_inventory_venue_section`
    FOREIGN KEY (`venue_section_id`) REFERENCES `venue_sections` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seat_holds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(190) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_id` BIGINT UNSIGNED NOT NULL,
  `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `status` ENUM('active', 'expired', 'released', 'converted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seat_holds_token` (`token`),
  KEY `idx_seat_holds_user_id` (`user_id`),
  KEY `idx_seat_holds_event_id` (`event_id`),
  KEY `idx_seat_holds_performance_id` (`performance_id`),
  KEY `idx_seat_holds_status` (`status`),
  KEY `idx_seat_holds_expires_at` (`expires_at`),
  CONSTRAINT `fk_seat_holds_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT `fk_seat_holds_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_seat_holds_performance`
    FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seat_hold_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `seat_hold_id` BIGINT UNSIGNED NOT NULL,
  `seat_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seat_hold_items_hold_seat` (`seat_hold_id`, `seat_id`),
  KEY `idx_seat_hold_items_seat_hold_id` (`seat_hold_id`),
  KEY `idx_seat_hold_items_seat_id` (`seat_id`),
  CONSTRAINT `fk_seat_hold_items_seat_hold`
    FOREIGN KEY (`seat_hold_id`) REFERENCES `seat_holds` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_seat_hold_items_seat`
    FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
