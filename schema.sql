-- SPA Database Schema
-- Run this in the 'spa' database

-- Users table (for authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `staff_id` VARCHAR(64) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrolment table (principal enrollees)
CREATE TABLE IF NOT EXISTS `enrolment` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `policy_no` VARCHAR(255) NOT NULL UNIQUE,
  `principal_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `organization_name` VARCHAR(255) DEFAULT NULL,
  `hcp` VARCHAR(255) DEFAULT NULL,
  `plan_type` VARCHAR(64) DEFAULT 'INDV',
  `staff_id` VARCHAR(64) DEFAULT NULL COMMENT 'ID of staff member who processed the enrolment',
  `photo_path` VARCHAR(1024) DEFAULT NULL,
  `id_document_path` VARCHAR(1024) DEFAULT NULL,
  `hmo_code` VARCHAR(10) DEFAULT NULL,
  `date_enrolled` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_policy_no` (`policy_no`),
  KEY `idx_date_enrolled` (`date_enrolled`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_plan_type_hmo` (`plan_type`, `hmo_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dependants table (family members)
CREATE TABLE IF NOT EXISTS `dependants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrolment_id` INT UNSIGNED NOT NULL,
  `policy_no` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `relationship` VARCHAR(64) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `photo_path` VARCHAR(1024) DEFAULT NULL COMMENT 'Passport photo for dependant',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_enrolment_idx` (`enrolment_id`),
  KEY `idx_policy_no` (`policy_no`),
  CONSTRAINT `fk_dependants_enrolment` FOREIGN KEY (`enrolment_id`)
    REFERENCES `enrolment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
