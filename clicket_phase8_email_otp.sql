-- CLICKET Phase 8: Email OTP Verification
-- Run this on existing databases if you do not rely on the runtime migration.

USE `clicket`;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `email_verified_at` DATETIME NULL AFTER `status`;

CREATE TABLE IF NOT EXISTS `email_otps` (
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
