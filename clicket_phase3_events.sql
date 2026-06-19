-- CLICKET Phase 3: Event Management
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: event records and event performance schedules.
-- Dependencies: Phase 1 staff_accounts, Phase 2 venues and venue_layouts.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `event_performances`;
DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_key` VARCHAR(190) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `type` VARCHAR(80) DEFAULT NULL,
  `artist` VARCHAR(160) DEFAULT NULL,
  `company` VARCHAR(160) DEFAULT NULL,
  `league` VARCHAR(120) DEFAULT NULL,
  `venue_id` BIGINT UNSIGNED NOT NULL,
  `venue_layout_id` BIGINT UNSIGNED NOT NULL,
  `poster_url` VARCHAR(500) DEFAULT NULL,
  `banner_url` VARCHAR(500) DEFAULT NULL,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `rating` DECIMAL(3,2) DEFAULT NULL,
  `status` ENUM('draft', 'published', 'paused', 'archived') NOT NULL DEFAULT 'draft',
  `created_by_staff_id` BIGINT UNSIGNED NOT NULL,
  `archive_requested_by` BIGINT UNSIGNED DEFAULT NULL,
  `archive_approved_by` BIGINT UNSIGNED DEFAULT NULL,
  `archived_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_events_event_key` (`event_key`),
  KEY `idx_events_venue_id` (`venue_id`),
  KEY `idx_events_venue_layout_id` (`venue_layout_id`),
  KEY `idx_events_created_by_staff_id` (`created_by_staff_id`),
  KEY `idx_events_archive_requested_by` (`archive_requested_by`),
  KEY `idx_events_archive_approved_by` (`archive_approved_by`),
  KEY `idx_events_status` (`status`),
  CONSTRAINT `fk_events_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_events_venue_layout`
    FOREIGN KEY (`venue_layout_id`) REFERENCES `venue_layouts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_events_created_by_staff`
    FOREIGN KEY (`created_by_staff_id`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_events_archive_requested_by`
    FOREIGN KEY (`archive_requested_by`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT `fk_events_archive_approved_by`
    FOREIGN KEY (`archive_approved_by`) REFERENCES `staff_accounts` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_performances` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `performance_date` DATE NOT NULL,
  `performance_time` TIME NOT NULL,
  `status` ENUM('scheduled', 'sold_out', 'cancelled', 'completed') NOT NULL DEFAULT 'scheduled',
  PRIMARY KEY (`id`),
  KEY `idx_event_performances_event_id` (`event_id`),
  KEY `idx_event_performances_status` (`status`),
  UNIQUE KEY `uq_event_performances_event_datetime` (`event_id`, `performance_date`, `performance_time`),
  CONSTRAINT `fk_event_performances_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
