-- CLICKET Consolidated Production Database Schema
-- Single source of truth for a fresh ClicKet database installation.
-- Includes identity, venues, events, reservations, orders/payments,
-- tickets/check-in, operations/audit, email OTP, and active-seat protection.
-- Compatible with MySQL / MariaDB in XAMPP and phpMyAdmin import.
-- Database: clicket
-- Character set: utf8mb4
-- Collation: utf8mb4_unicode_ci
--
-- WARNING: This is a clean-install script. It drops the ClicKet tables below
-- before recreating them, so do not run it against a database whose data you
-- need to keep without taking a backup first.

CREATE DATABASE IF NOT EXISTS `clicket`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `clicket`;

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `archive_records`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `tier_blocks`;
DROP TABLE IF EXISTS `seat_blocks`;
DROP TABLE IF EXISTS `checkin_logs`;
DROP TABLE IF EXISTS `ticket_print_logs`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `payment_proofs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_seats`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `seat_hold_items`;
DROP TABLE IF EXISTS `seat_holds`;
DROP TABLE IF EXISTS `event_section_inventory`;
DROP TABLE IF EXISTS `event_tier_settings`;
DROP TABLE IF EXISTS `event_performances`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `staff_venue_assignments`;
DROP TABLE IF EXISTS `seats`;
DROP TABLE IF EXISTS `venue_non_seat_area_points`;
DROP TABLE IF EXISTS `venue_non_seat_areas`;
DROP TABLE IF EXISTS `venue_section_points`;
DROP TABLE IF EXISTS `venue_sections`;
DROP TABLE IF EXISTS `venue_tiers`;
DROP TABLE IF EXISTS `venue_layouts`;
DROP TABLE IF EXISTS `venue_aliases`;
DROP TABLE IF EXISTS `venues`;
DROP TABLE IF EXISTS `staff_accounts`;
DROP TABLE IF EXISTS `email_otps`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `email_verified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_otps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(190) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_otps_email` (`email`),
  KEY `idx_email_otps_user_id` (`user_id`),
  KEY `idx_email_otps_expires_at` (`expires_at`),
  CONSTRAINT `fk_email_otps_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `staff_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'organizer') NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_accounts_email` (`email`),
  KEY `idx_staff_accounts_role` (`role`),
  KEY `idx_staff_accounts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venues` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venues_slug` (`slug`),
  KEY `idx_venues_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_aliases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` BIGINT UNSIGNED NOT NULL,
  `alias` VARCHAR(190) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_aliases_alias` (`alias`),
  UNIQUE KEY `uq_venue_aliases_venue_alias` (`venue_id`, `alias`),
  KEY `idx_venue_aliases_venue_id` (`venue_id`),
  CONSTRAINT `fk_venue_aliases_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_layouts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` BIGINT UNSIGNED NOT NULL,
  `layout_key` VARCHAR(120) NOT NULL,
  `variant` VARCHAR(80) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 0,
  `svg_file` VARCHAR(255) NOT NULL,
  `map_type` VARCHAR(80) NOT NULL DEFAULT 'svg',
  `stage_label` VARCHAR(120) DEFAULT NULL,
  `subtitle` VARCHAR(190) DEFAULT NULL,
  `viewbox_x` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `viewbox_y` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `viewbox_width` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `viewbox_height` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_layouts_layout_key` (`layout_key`),
  UNIQUE KEY `uq_venue_layouts_venue_variant_category` (`venue_id`, `variant`, `category`),
  KEY `idx_venue_layouts_venue_id` (`venue_id`),
  KEY `idx_venue_layouts_status` (`status`),
  CONSTRAINT `fk_venue_layouts_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_tiers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_layout_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(160) NOT NULL,
  `color` CHAR(7) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `default_status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_tiers_layout_slug` (`venue_layout_id`, `slug`),
  KEY `idx_venue_tiers_venue_layout_id` (`venue_layout_id`),
  KEY `idx_venue_tiers_default_status` (`default_status`),
  CONSTRAINT `fk_venue_tiers_layout`
    FOREIGN KEY (`venue_layout_id`) REFERENCES `venue_layouts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_sections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_layout_id` BIGINT UNSIGNED NOT NULL,
  `tier_id` BIGINT UNSIGNED NOT NULL,
  `svg_polygon_id` VARCHAR(160) NOT NULL,
  `section_number` VARCHAR(60) DEFAULT NULL,
  `label` VARCHAR(120) NOT NULL,
  `category_key` VARCHAR(120) DEFAULT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 0,
  `map_color` CHAR(7) DEFAULT NULL,
  `zone` VARCHAR(120) DEFAULT NULL,
  `is_seating_section` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_sections_layout_polygon` (`venue_layout_id`, `svg_polygon_id`),
  KEY `idx_venue_sections_venue_layout_id` (`venue_layout_id`),
  KEY `idx_venue_sections_tier_id` (`tier_id`),
  KEY `idx_venue_sections_category_key` (`category_key`),
  CONSTRAINT `fk_venue_sections_layout`
    FOREIGN KEY (`venue_layout_id`) REFERENCES `venue_layouts` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_venue_sections_tier`
    FOREIGN KEY (`tier_id`) REFERENCES `venue_tiers` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_section_points` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` BIGINT UNSIGNED NOT NULL,
  `point_order` SMALLINT UNSIGNED NOT NULL,
  `x` DECIMAL(10,2) NOT NULL,
  `y` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_section_points_section_order` (`section_id`, `point_order`),
  KEY `idx_venue_section_points_section_id` (`section_id`),
  CONSTRAINT `fk_venue_section_points_section`
    FOREIGN KEY (`section_id`) REFERENCES `venue_sections` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_non_seat_areas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_layout_id` BIGINT UNSIGNED NOT NULL,
  `svg_polygon_id` VARCHAR(160) NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  `show_label` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_non_seat_areas_layout_polygon` (`venue_layout_id`, `svg_polygon_id`),
  KEY `idx_venue_non_seat_areas_venue_layout_id` (`venue_layout_id`),
  CONSTRAINT `fk_venue_non_seat_areas_layout`
    FOREIGN KEY (`venue_layout_id`) REFERENCES `venue_layouts` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_non_seat_area_points` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `area_id` BIGINT UNSIGNED NOT NULL,
  `point_order` SMALLINT UNSIGNED NOT NULL,
  `x` DECIMAL(10,2) NOT NULL,
  `y` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_non_seat_area_points_area_order` (`area_id`, `point_order`),
  KEY `idx_venue_non_seat_area_points_area_id` (`area_id`),
  CONSTRAINT `fk_venue_non_seat_area_points_area`
    FOREIGN KEY (`area_id`) REFERENCES `venue_non_seat_areas` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_section_id` BIGINT UNSIGNED NOT NULL,
  `seat_code` VARCHAR(120) NOT NULL,
  `row_label` VARCHAR(40) DEFAULT NULL,
  `seat_number` VARCHAR(40) DEFAULT NULL,
  `x` DECIMAL(10,2) DEFAULT NULL,
  `y` DECIMAL(10,2) DEFAULT NULL,
  `status` ENUM('available', 'held', 'sold', 'blocked', 'accessible', 'complimentary') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seats_section_seat_code` (`venue_section_id`, `seat_code`),
  KEY `idx_seats_venue_section_id` (`venue_section_id`),
  KEY `idx_seats_status` (`status`),
  CONSTRAINT `fk_seats_section`
    FOREIGN KEY (`venue_section_id`) REFERENCES `venue_sections` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

INSERT INTO `staff_accounts`
  (`name`, `email`, `password_hash`, `role`, `status`)
VALUES
  ('CLICKET Admin', 'admin@clicket.local', '$2y$12$uhnRhQEY4wealm4tsjAHSOXozs2btTP.C1UtuNBYWO6VT2tFrWpWi', 'admin', 'active'),
  ('CLICKET Organizer', 'organizer@clicket.local', '$2y$12$UR25sCoZSE.4YZyLVvmx1u6AsIYkZvygjV8KWDgptSNT2ctGV/ty6', 'organizer', 'active');
