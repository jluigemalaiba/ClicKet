-- CLICKET Phase 2: Venue Management and Seating Layout
-- Compatible with MySQL / phpMyAdmin import.
-- Scope: venue module only.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `seats`;
DROP TABLE IF EXISTS `venue_non_seat_area_points`;
DROP TABLE IF EXISTS `venue_non_seat_areas`;
DROP TABLE IF EXISTS `venue_section_points`;
DROP TABLE IF EXISTS `venue_sections`;
DROP TABLE IF EXISTS `venue_tiers`;
DROP TABLE IF EXISTS `venue_layouts`;
DROP TABLE IF EXISTS `venue_aliases`;
DROP TABLE IF EXISTS `venues`;

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
