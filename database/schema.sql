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
  `lastname` varchar(255) NOT NULL COMMENT 'Student last name',
  `firstname` varchar(255) NOT NULL COMMENT 'Student first name',
  `strand` varchar(255) DEFAULT NULL COMMENT 'Student strand/track',
  `year` varchar(255) DEFAULT NULL COMMENT 'Student year level',
  `gender` enum('Male','Female','Other') NOT NULL COMMENT 'Student gender',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `has_voted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether student has completed voting',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_usn_unique` (`usn`),
  KEY `idx_students_has_voted` (`has_voted`),
  KEY `idx_students_name` (`lastname`, `firstname`),
  KEY `idx_students_year` (`year`),
  KEY `idx_students_strand` (`strand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: votes
-- Purpose: Stores anonymous votes (SECURE - cannot trace to student)
-- =====================================================
CREATE TABLE IF NOT EXISTS `votes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `position_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Position voted for',
  `candidate_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Candidate selected',
  `vote_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash for vote verification',
  `encrypted_voter_id` varchar(255) NOT NULL COMMENT 'Encrypted student ID (admin key required)',
  `voted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When vote was cast',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `votes_vote_hash_unique` (`vote_hash`),
  KEY `idx_votes_election_position_candidate` (`election_id`, `position_id`, `candidate_id`),
  KEY `idx_votes_voted_at` (`voted_at`),
  CONSTRAINT `votes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: voting_sessions
-- Purpose: Track voting sessions (prevents double voting, enables manual count)
-- =====================================================
CREATE TABLE IF NOT EXISTS `voting_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `student_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Student who voted',
  `session_token` varchar(64) NOT NULL COMMENT 'Unique session token',
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When session started',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'When voting completed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address',
  `user_agent` text DEFAULT NULL COMMENT 'Browser user agent',
  `status` enum('started','completed','abandoned','invalid') NOT NULL DEFAULT 'started',
  `ballot_number` varchar(20) DEFAULT NULL COMMENT 'Physical ballot number for manual counting',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voting_sessions_session_token_unique` (`session_token`),
  UNIQUE KEY `voting_sessions_ballot_number_unique` (`ballot_number`),
  UNIQUE KEY `voting_sessions_unique` (`election_id`, `student_id`),
  KEY `voting_sessions_student_id_foreign` (`student_id`),
  KEY `idx_voting_sessions_status` (`status`),
  KEY `idx_voting_sessions_completed_at` (`completed_at`),
  CONSTRAINT `voting_sessions_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voting_sessions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: vote_audit_log
-- Purpose: Immutable audit trail for all voting activities
-- =====================================================
CREATE TABLE IF NOT EXISTS `vote_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `action` enum('vote_cast','vote_verified','session_started','session_completed','session_abandoned','suspicious_activity','admin_access','results_viewed') NOT NULL,
  `actor_type` varchar(20) NOT NULL COMMENT 'student or admin',
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID of actor',
  `details` text DEFAULT NULL COMMENT 'JSON data about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address',
  `user_agent` text DEFAULT NULL COMMENT 'Browser user agent',
  `previous_hash` varchar(64) DEFAULT NULL COMMENT 'Hash of previous log entry',
  `entry_hash` varchar(64) NOT NULL COMMENT 'Hash of this entry',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_election_action` (`election_id`, `action`),
  KEY `idx_audit_created_at` (`created_at`),
  KEY `idx_audit_actor` (`actor_type`, `actor_id`),
  CONSTRAINT `vote_audit_log_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: manual_count_records
-- Purpose: Manual counting backup and verification
-- =====================================================
CREATE TABLE IF NOT EXISTS `manual_count_records` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `position_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Position reference',
  `candidate_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Candidate reference',
  `manual_votes` int(11) NOT NULL DEFAULT 0 COMMENT 'Manually counted votes',
  `system_votes` int(11) NOT NULL DEFAULT 0 COMMENT 'System counted votes',
  `discrepancy` int(11) NOT NULL DEFAULT 0 COMMENT 'Difference between counts',
  `counted_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin who counted',
  `counted_at` timestamp NULL DEFAULT NULL COMMENT 'When manual count was done',
  `verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether verified',
  `verified_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin who verified',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'When verified',
  `notes` text DEFAULT NULL COMMENT 'Notes about discrepancies',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `manual_count_unique` (`election_id`, `position_id`, `candidate_id`),
  KEY `manual_count_records_position_id_foreign` (`position_id`),
  KEY `manual_count_records_candidate_id_foreign` (`candidate_id`),
  KEY `manual_count_records_counted_by_admin_id_foreign` (`counted_by_admin_id`),
  KEY `manual_count_records_verified_by_admin_id_foreign` (`verified_by_admin_id`),
  CONSTRAINT `manual_count_records_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manual_count_records_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manual_count_records_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manual_count_records_counted_by_admin_id_foreign` FOREIGN KEY (`counted_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manual_count_records_verified_by_admin_id_foreign` FOREIGN KEY (`verified_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: vote_verification_codes
-- Purpose: Voter receipt system for verification
-- =====================================================
CREATE TABLE IF NOT EXISTS `vote_verification_codes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `voting_session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Voting session reference',
  `verification_code` varchar(20) NOT NULL COMMENT 'Code given to voter as receipt',
  `total_votes_cast` int(11) NOT NULL COMMENT 'Number of votes cast',
  `voted_timestamp` timestamp NOT NULL COMMENT 'When they voted',
  `verified_by_voter` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether voter verified',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'When verified',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vote_verification_codes_verification_code_unique` (`verification_code`),
  KEY `vote_verification_codes_election_id_foreign` (`election_id`),
  KEY `vote_verification_codes_voting_session_id_foreign` (`voting_session_id`),
  CONSTRAINT `vote_verification_codes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vote_verification_codes_voting_session_id_foreign` FOREIGN KEY (`voting_session_id`) REFERENCES `voting_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: election_integrity_checks
-- Purpose: Regular integrity checks to detect tampering
-- =====================================================
CREATE TABLE IF NOT EXISTS `election_integrity_checks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Election reference',
  `check_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When check was performed',
  `total_votes_expected` int(11) NOT NULL COMMENT 'Based on voting sessions',
  `total_votes_counted` int(11) NOT NULL COMMENT 'Actual votes in database',
  `total_students_voted` int(11) NOT NULL COMMENT 'Unique students who voted',
  `integrity_passed` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether check passed',
  `issues_found` text DEFAULT NULL COMMENT 'JSON array of issues',
  `database_hash` varchar(64) NOT NULL COMMENT 'Hash of all votes',
  `previous_check_hash` varchar(64) DEFAULT NULL COMMENT 'Hash of previous check',
  `performed_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin who performed check',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `election_integrity_checks_election_id_foreign` (`election_id`),
  KEY `election_integrity_checks_performed_by_admin_id_foreign` (`performed_by_admin_id`),
  KEY `idx_integrity_check_timestamp` (`check_timestamp`),
  KEY `idx_integrity_passed` (`integrity_passed`),
  CONSTRAINT `election_integrity_checks_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `election_integrity_checks_performed_by_admin_id_foreign` FOREIGN KEY (`performed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
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
