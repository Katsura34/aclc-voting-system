-- =====================================================
-- ACLC Voting System - Complete Database Schema
-- =====================================================
-- This file contains the complete database structure for the ACLC Voting System
-- Created: 2026-02-05
-- Database: MySQL 8.0+ / MariaDB 10.3+
-- =====================================================

-- Set database settings
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- TABLE: elections
-- Purpose: Stores election information and settings
-- =====================================================
CREATE TABLE IF NOT EXISTS `elections` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Election title/name',
  `description` text DEFAULT NULL COMMENT 'Election description',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether election is currently active',
  `start_date` datetime NOT NULL COMMENT 'Election start date and time',
  `end_date` datetime NOT NULL COMMENT 'Election end date and time',
  `show_live_results` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Show results in real-time',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_elections_is_active` (`is_active`),
  KEY `idx_elections_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: positions
-- Purpose: Stores positions available in an election
-- =====================================================
CREATE TABLE IF NOT EXISTS `positions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to election',
  `name` varchar(255) NOT NULL COMMENT 'Position name (e.g., President, Vice President)',
  `max_winners` int(11) NOT NULL DEFAULT 1 COMMENT 'Maximum number of winners for this position',
  `order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order in voting form',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `positions_election_id_foreign` (`election_id`),
  KEY `idx_positions_order` (`order`),
  CONSTRAINT `positions_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: parties
-- Purpose: Stores political parties/groups
-- =====================================================
CREATE TABLE IF NOT EXISTS `parties` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Party full name',
  `acronym` varchar(10) NOT NULL COMMENT 'Party acronym/short name',
  `color` varchar(255) DEFAULT NULL COMMENT 'Party color (hex code)',
  `logo` varchar(255) DEFAULT NULL COMMENT 'Path to party logo image',
  `description` text DEFAULT NULL COMMENT 'Party description/platform',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: candidates
-- Purpose: Stores candidate information
-- =====================================================
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL COMMENT 'Candidate first name',
  `last_name` varchar(255) NOT NULL COMMENT 'Candidate last name',
  `position_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Position they are running for',
  `party_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Party affiliation (nullable for independent)',
  `course` varchar(255) DEFAULT NULL COMMENT 'Student course/program',
  `year_level` varchar(255) DEFAULT NULL COMMENT 'Student year level',
  `bio` text DEFAULT NULL COMMENT 'Candidate biography/platform',
  `photo_path` varchar(255) DEFAULT NULL COMMENT 'Path to candidate photo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidates_position_id_foreign` (`position_id`),
  KEY `candidates_party_id_foreign` (`party_id`),
  KEY `idx_candidates_name` (`last_name`, `first_name`),
  CONSTRAINT `candidates_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidates_party_id_foreign` FOREIGN KEY (`party_id`) REFERENCES `parties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: admins
-- Purpose: Stores administrator accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT 'Admin username (unique)',
  `name` varchar(255) NOT NULL COMMENT 'Admin full name',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: students
-- Purpose: Stores student voter accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usn` varchar(255) NOT NULL COMMENT 'Student number (unique)',
  `name` varchar(255) NOT NULL COMMENT 'Student full name',
  `email` varchar(255) NOT NULL COMMENT 'Student email address',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `has_voted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether student has completed voting',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_usn_unique` (`usn`),
  UNIQUE KEY `students_email_unique` (`email`),
  KEY `idx_students_has_voted` (`has_voted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: votes
-- Purpose: Stores individual votes cast by students
-- =====================================================
CREATE TABLE IF NOT EXISTS `votes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `student_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Student who voted',
  `position_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Position voted for',
  `candidate_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Candidate selected',
  `voted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When vote was cast',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `votes_election_student_position_unique` (`election_id`, `student_id`, `position_id`),
  KEY `votes_student_id_foreign` (`student_id`),
  KEY `votes_position_id_foreign` (`position_id`),
  KEY `votes_candidate_id_foreign` (`candidate_id`),
  KEY `idx_votes_election` (`election_id`),
  KEY `idx_votes_candidate` (`candidate_id`),
  KEY `idx_votes_position` (`position_id`),
  CONSTRAINT `votes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: voting_records
-- Purpose: Audit trail of who voted (not who they voted for)
-- =====================================================
CREATE TABLE IF NOT EXISTS `voting_records` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `student_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Student who voted',
  `voted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When student completed voting',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of voter',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voting_records_election_student_unique` (`election_id`, `student_id`),
  KEY `voting_records_student_id_foreign` (`student_id`),
  KEY `idx_voting_records_voted_at` (`voted_at`),
  CONSTRAINT `voting_records_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voting_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: cache
-- Purpose: Application cache storage
-- =====================================================
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: cache_locks
-- Purpose: Cache locking mechanism
-- =====================================================
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: sessions
-- Purpose: User session management
-- =====================================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: password_reset_tokens
-- Purpose: Password reset token storage
-- =====================================================
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- SAMPLE DATA QUERIES
-- =====================================================

-- Create a sample election
-- INSERT INTO elections (title, description, is_active, start_date, end_date, show_live_results, created_at, updated_at) 
-- VALUES ('ACLC Student Council Election 2025', 'Annual election for ACLC Student Council officers', 1, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW(), NOW());

-- Create sample positions
-- INSERT INTO positions (election_id, name, max_winners, `order`, created_at, updated_at) VALUES
-- (1, 'President', 1, 1, NOW(), NOW()),
-- (1, 'Vice President', 1, 2, NOW(), NOW()),
-- (1, 'Secretary', 1, 3, NOW(), NOW()),
-- (1, 'Treasurer', 1, 4, NOW(), NOW()),
-- (1, 'Representative', 3, 5, NOW(), NOW());

-- =====================================================
-- END OF SCHEMA
-- =====================================================
