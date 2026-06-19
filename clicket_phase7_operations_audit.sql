-- CLICKET Phase 7: Operations Management, Audit Logging, Archive Tracking, Venue Permissions, Favorites, and Inventory Restriction Controls
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: venue staff permissions, seat and tier restrictions, favorites, audit logs, and archive records.
-- Dependencies: Phase 1 users and staff_accounts, Phase 2 venues, venue_tiers, and seats, Phase 3 events and event_performances.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `archive_records`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `tier_blocks`;
DROP TABLE IF EXISTS `seat_blocks`;
DROP TABLE IF EXISTS `staff_venue_assignments`;

CREATE TABLE `staff_venue_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` BIGINT UNSIGNED NOT NULL,
  `venue_id` BIGINT UNSIGNED NOT NULL,
  `create_events` TINYINT(1) NOT NULL DEFAULT 0,
  `archive_events` TINYINT(1) NOT NULL DEFAULT 0,
  `manage_tiers` TINYINT(1) NOT NULL DEFAULT 0,
  `manage_seats` TINYINT(1) NOT NULL DEFAULT 0,
  `review_payments` TINYINT(1) NOT NULL DEFAULT 0,
  `print_tickets` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_venue_assignments_staff_venue` (`staff_id`, `venue_id`),
  KEY `idx_staff_venue_assignments_staff_id` (`staff_id`),
  KEY `idx_staff_venue_assignments_venue_id` (`venue_id`),
  CONSTRAINT `fk_staff_venue_assignments_staff`
    FOREIGN KEY (`staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_staff_venue_assignments_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seat_blocks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_id` BIGINT UNSIGNED NOT NULL,
  `seat_id` BIGINT UNSIGNED NOT NULL,
  `blocked_by_staff_id` BIGINT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'released') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `released_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seat_blocks_event_id` (`event_id`),
  KEY `idx_seat_blocks_performance_id` (`performance_id`),
  KEY `idx_seat_blocks_seat_id` (`seat_id`),
  KEY `idx_seat_blocks_blocked_by_staff_id` (`blocked_by_staff_id`),
  KEY `idx_seat_blocks_status` (`status`),
  CONSTRAINT `fk_seat_blocks_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_seat_blocks_performance`
    FOREIGN KEY (`performance_id`) REFERENCES `event_performances` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_seat_blocks_seat`
    FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_seat_blocks_blocked_by_staff`
    FOREIGN KEY (`blocked_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tier_blocks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `tier_id` BIGINT UNSIGNED NOT NULL,
  `blocked_by_staff_id` BIGINT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'released') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `released_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tier_blocks_event_id` (`event_id`),
  KEY `idx_tier_blocks_tier_id` (`tier_id`),
  KEY `idx_tier_blocks_blocked_by_staff_id` (`blocked_by_staff_id`),
  KEY `idx_tier_blocks_status` (`status`),
  CONSTRAINT `fk_tier_blocks_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_tier_blocks_tier`
    FOREIGN KEY (`tier_id`) REFERENCES `venue_tiers` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_tier_blocks_blocked_by_staff`
    FOREIGN KEY (`blocked_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `favorites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_favorites_user_event` (`user_id`, `event_id`),
  KEY `idx_favorites_user_id` (`user_id`),
  KEY `idx_favorites_event_id` (`event_id`),
  CONSTRAINT `fk_favorites_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_favorites_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `actor_role` VARCHAR(80) NOT NULL,
  `action_type` VARCHAR(120) NOT NULL,
  `entity_type` VARCHAR(120) NOT NULL,
  `entity_id` BIGINT UNSIGNED DEFAULT NULL,
  `old_value` LONGTEXT DEFAULT NULL,
  `new_value` LONGTEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_logs_actor_staff_id` (`actor_staff_id`),
  KEY `idx_audit_logs_action_type` (`action_type`),
  KEY `idx_audit_logs_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_logs_created_at` (`created_at`),
  CONSTRAINT `fk_audit_logs_actor_staff`
    FOREIGN KEY (`actor_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `archive_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(120) NOT NULL,
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `archived_by_staff_id` BIGINT UNSIGNED NOT NULL,
  `approved_by_staff_id` BIGINT UNSIGNED DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `archived_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `restored_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_archive_records_entity` (`entity_type`, `entity_id`),
  KEY `idx_archive_records_archived_by_staff_id` (`archived_by_staff_id`),
  KEY `idx_archive_records_approved_by_staff_id` (`approved_by_staff_id`),
  KEY `idx_archive_records_archived_at` (`archived_at`),
  CONSTRAINT `fk_archive_records_archived_by_staff`
    FOREIGN KEY (`archived_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_archive_records_approved_by_staff`
    FOREIGN KEY (`approved_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
