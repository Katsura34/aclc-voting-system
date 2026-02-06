# ACLC Voting System - Database Documentation

## Overview

This document provides comprehensive documentation for the ACLC Voting System database structure. The system is designed to manage student council elections with support for multiple positions, candidates, parties, and secure voting.

## Database Schema

### Table of Contents
1. [Core Tables](#core-tables)
2. [User Management Tables](#user-management-tables)
3. [Voting Tables](#voting-tables)
4. [System Tables](#system-tables)
5. [Relationships](#relationships)
6. [Indexes](#indexes)

---

## Core Tables

### elections
Stores information about elections.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| title | varchar(255) | NO | - | Election title/name |
| description | text | YES | NULL | Election description |
| is_active | tinyint(1) | NO | 0 | Whether election is currently active |
| start_date | datetime | NO | - | Election start date and time |
| end_date | datetime | NO | - | Election end date and time |
| show_live_results | tinyint(1) | NO | 0 | Show results in real-time |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_elections_is_active` (`is_active`)
- INDEX `idx_elections_dates` (`start_date`, `end_date`)

**Business Rules:**
- Only one election should be active at a time
- `end_date` must be after `start_date`
- Elections cannot be deleted if votes exist

---

### positions
Stores positions available in an election (e.g., President, Vice President).

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| election_id | bigint UNSIGNED | NO | - | Reference to election |
| name | varchar(255) | NO | - | Position name |
| max_winners | int | NO | 1 | Maximum number of winners |
| order | int | NO | 0 | Display order in voting form |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Foreign Keys:**
- `election_id` → `elections.id` (ON DELETE CASCADE)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_positions_order` (`order`)

**Business Rules:**
- `max_winners` typically 1, but can be higher for representatives
- Positions are ordered by the `order` field in the voting interface

---

### parties
Stores political parties or groups.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | Party full name |
| acronym | varchar(10) | NO | - | Party acronym/short name |
| color | varchar(255) | YES | NULL | Party color (hex code) |
| logo | varchar(255) | YES | NULL | Path to party logo image |
| description | text | YES | NULL | Party description/platform |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Indexes:**
- PRIMARY KEY (`id`)

**Business Rules:**
- Parties can be shared across multiple elections
- Candidates can be independent (no party affiliation)

---

### candidates
Stores candidate information.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| first_name | varchar(255) | NO | - | Candidate first name |
| last_name | varchar(255) | NO | - | Candidate last name |
| position_id | bigint UNSIGNED | NO | - | Position running for |
| party_id | bigint UNSIGNED | YES | NULL | Party affiliation (nullable) |
| course | varchar(255) | YES | NULL | Student course/program |
| year_level | varchar(255) | YES | NULL | Student year level |
| bio | text | YES | NULL | Candidate biography/platform |
| photo_path | varchar(255) | YES | NULL | Path to candidate photo |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Foreign Keys:**
- `position_id` → `positions.id` (ON DELETE CASCADE)
- `party_id` → `parties.id` (ON DELETE SET NULL)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_candidates_name` (`last_name`, `first_name`)

**Business Rules:**
- Each candidate runs for one specific position
- Independent candidates have `party_id = NULL`
- Photos should be stored in `storage/app/public/candidates/`

---

## User Management Tables

### admins
Stores administrator accounts.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| username | varchar(255) | NO | - | Admin username (unique) |
| name | varchar(255) | NO | - | Admin full name |
| password | varchar(255) | NO | - | Hashed password |
| remember_token | varchar(100) | YES | NULL | Remember me token |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `admins_username_unique` (`username`)

**Business Rules:**
- Passwords are hashed using bcrypt
- Admins have full system access

---

### students
Stores student voter accounts.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| usn | varchar(255) | NO | - | Student number (unique) |
| name | varchar(255) | NO | - | Student full name |
| email | varchar(255) | NO | - | Student email address |
| password | varchar(255) | NO | - | Hashed password |
| has_voted | tinyint(1) | NO | 0 | Whether student has voted |
| remember_token | varchar(100) | YES | NULL | Remember me token |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `students_usn_unique` (`usn`)
- UNIQUE KEY `students_email_unique` (`email`)
- INDEX `idx_students_has_voted` (`has_voted`)

**Business Rules:**
- USN (University Student Number) is the primary identifier
- `has_voted` flag prevents multiple voting
- Students can only vote once per election

---

## Voting Tables

### votes
Stores individual votes cast by students.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| election_id | bigint UNSIGNED | NO | - | Election reference |
| student_id | bigint UNSIGNED | NO | - | Student who voted |
| position_id | bigint UNSIGNED | NO | - | Position voted for |
| candidate_id | bigint UNSIGNED | NO | - | Candidate selected |
| voted_at | timestamp | NO | CURRENT | When vote was cast |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Foreign Keys:**
- `election_id` → `elections.id` (ON DELETE CASCADE)
- `student_id` → `students.id` (ON DELETE CASCADE)
- `position_id` → `positions.id` (ON DELETE CASCADE)
- `candidate_id` → `candidates.id` (ON DELETE CASCADE)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `votes_election_student_position_unique` (`election_id`, `student_id`, `position_id`)
- INDEX `idx_votes_election` (`election_id`)
- INDEX `idx_votes_candidate` (`candidate_id`)
- INDEX `idx_votes_position` (`position_id`)

**Business Rules:**
- One vote per student per position per election (enforced by unique constraint)
- Votes are anonymous after casting (no way to trace back to specific student)
- All votes are encrypted in storage

---

### voting_records
Audit trail of who voted (not who they voted for).

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | bigint UNSIGNED | NO | AUTO | Primary key |
| election_id | bigint UNSIGNED | NO | - | Election reference |
| student_id | bigint UNSIGNED | NO | - | Student who voted |
| voted_at | timestamp | NO | CURRENT | When student completed voting |
| ip_address | varchar(45) | YES | NULL | IP address of voter |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Record update timestamp |

**Foreign Keys:**
- `election_id` → `elections.id` (ON DELETE CASCADE)
- `student_id` → `students.id` (ON DELETE CASCADE)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `voting_records_election_student_unique` (`election_id`, `student_id`)
- INDEX `idx_voting_records_voted_at` (`voted_at`)

**Business Rules:**
- Records only that a student voted, not their choices
- Used for election integrity and auditing
- One record per student per election

---

## System Tables

### sessions
Manages user session data.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | varchar(255) | NO | - | Session ID |
| user_id | bigint UNSIGNED | YES | NULL | Authenticated user ID |
| ip_address | varchar(45) | YES | NULL | User IP address |
| user_agent | text | YES | NULL | User browser agent |
| payload | longtext | NO | - | Session data |
| last_activity | int | NO | - | Last activity timestamp |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `sessions_last_activity_index` (`last_activity`)

---

### cache
Application cache storage.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| key | varchar(255) | NO | - | Cache key |
| value | mediumtext | NO | - | Cached value |
| expiration | int | NO | - | Expiration timestamp |

**Indexes:**
- PRIMARY KEY (`key`)

---

### password_reset_tokens
Stores password reset tokens.

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| email | varchar(255) | NO | - | User email |
| token | varchar(255) | NO | - | Reset token |
| created_at | timestamp | YES | NULL | Token creation time |

**Indexes:**
- PRIMARY KEY (`email`)

---

## Entity Relationship Diagram

```
┌─────────────┐
│  elections  │
└──────┬──────┘
       │
       │ 1:N
       ├──────────────┐
       │              │
       ▼              ▼
┌─────────────┐  ┌─────────────┐
│  positions  │  │    votes    │
└──────┬──────┘  └──────┬──────┘
       │                │
       │ 1:N            │ N:1
       ▼                ▼
┌─────────────┐  ┌─────────────┐
│ candidates  │  │  students   │
└──────┬──────┘  └─────────────┘
       │
       │ N:1
       ▼
┌─────────────┐
│   parties   │
└─────────────┘
```

## Relationships

### Election → Positions (1:N)
- One election has many positions
- Deleting an election cascades to delete all positions

### Position → Candidates (1:N)
- One position has many candidates
- Deleting a position cascades to delete all candidates

### Party → Candidates (1:N)
- One party can have many candidates
- Deleting a party sets candidate party_id to NULL

### Election → Votes (1:N)
- One election has many votes
- Deleting an election cascades to delete all votes

### Student → Votes (1:N)
- One student can cast many votes (one per position)
- Deleting a student cascades to delete their votes

### Position → Votes (1:N)
- One position receives many votes
- Deleting a position cascades to delete votes for that position

### Candidate → Votes (1:N)
- One candidate receives many votes
- Deleting a candidate cascades to delete votes for them

---

## Indexes

Performance indexes have been added to optimize common queries:

1. **elections**: Active status and date range queries
2. **positions**: Ordering queries
3. **candidates**: Name-based searches
4. **students**: Voting status checks
5. **votes**: Election-wide tallying, candidate vote counts
6. **voting_records**: Audit trail queries by date

---

## Database Setup

### For Fresh Installation

1. **Create Database:**
```sql
CREATE DATABASE aclc_voting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Import Schema:**
```bash
mysql -u username -p aclc_voting < database/schema.sql
```

### Using Laravel Migrations

1. **Run migrations:**
```bash
php artisan migrate
```

2. **Seed sample data:**
```bash
php artisan db:seed --class=VotingSystemSeeder
```

---

## Data Import/Export

### Export Structure
```bash
mysqldump -u username -p --no-data aclc_voting > schema.sql
```

### Export Data
```bash
mysqldump -u username -p aclc_voting > backup.sql
```

### Import
```bash
mysql -u username -p aclc_voting < backup.sql
```

---

## Security Considerations

1. **Password Storage**: All passwords are hashed using bcrypt (Laravel default)
2. **Vote Anonymity**: Votes cannot be traced back to individual students
3. **Access Control**: 
   - Admins have full access
   - Students can only vote, not view results until allowed
4. **SQL Injection Prevention**: All queries use parameterized statements
5. **Foreign Key Constraints**: Maintain referential integrity

---

## Backup Strategy

### Recommended Schedule:
- **Before Election**: Full backup
- **During Election**: Hourly incremental backups
- **After Election**: Full backup and archive

### Backup Commands:
```bash
# Full backup
php artisan backup:run

# Database only
mysqldump -u username -p aclc_voting > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## Query Examples

### Get Active Election with Positions
```sql
SELECT e.*, p.* 
FROM elections e
JOIN positions p ON p.election_id = e.id
WHERE e.is_active = 1
ORDER BY p.order;
```

### Get Vote Count by Candidate
```sql
SELECT 
    c.first_name,
    c.last_name,
    p.name as position,
    COUNT(v.id) as vote_count
FROM candidates c
LEFT JOIN votes v ON v.candidate_id = c.id
JOIN positions p ON c.position_id = p.id
WHERE p.election_id = 1
GROUP BY c.id, c.first_name, c.last_name, p.name
ORDER BY p.order, vote_count DESC;
```

### Get Voting Participation Rate
```sql
SELECT 
    COUNT(DISTINCT vr.student_id) as voters,
    COUNT(s.id) as total_students,
    ROUND(COUNT(DISTINCT vr.student_id) / COUNT(s.id) * 100, 2) as participation_rate
FROM students s
LEFT JOIN voting_records vr ON vr.student_id = s.id AND vr.election_id = 1;
```

---

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**
   - Solution: Ensure parent records exist before inserting child records
   - Order: elections → positions → candidates
   - Order: students → votes

2. **Duplicate Vote Attempts**
   - Protected by unique constraint on votes table
   - Also checked in application logic

3. **Performance Issues**
   - Check if indexes are properly created
   - Run `ANALYZE TABLE` on large tables
   - Consider caching active election data

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-01 | Initial schema |
| 1.1 | 2025-11-03 | Added performance indexes |
| 1.2 | 2026-01-08 | Separated admin/student tables |
| 1.3 | 2026-01-08 | Added voting_records table |

---

## Support

For database-related issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check database logs
3. Run migrations fresh: `php artisan migrate:fresh --seed`

---

**Last Updated**: 2026-02-05
