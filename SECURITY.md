# ACLC Voting System - Security Architecture

## Overview

This document details the comprehensive security measures implemented in the ACLC Voting System to ensure election integrity, prevent hacking, and enable manual counting as a backup.

## Table of Contents

1. [Separate Login System](#separate-login-system)
2. [Vote Anonymization](#vote-anonymization)
3. [Audit Trail](#audit-trail)
4. [Manual Counting System](#manual-counting-system)
5. [Rate Limiting & Brute Force Protection](#rate-limiting--brute-force-protection)
6. [Session Security](#session-security)
7. [Database Security](#database-security)
8. [Integrity Checking](#integrity-checking)

---

## Separate Login System

### Why Separate Login Pages?

**Security Benefits:**
1. **Obscurity**: Admin login URL (`/admin/login`) is hidden from students
2. **Reduced Attack Surface**: Attackers don't know where admin panel is located
3. **Different Security Rules**: Stricter rate limiting for admin (5 attempts vs 10 for students)
4. **Prevents Confusion**: Students can't accidentally try admin access
5. **Better Logging**: Separate logs for admin vs student access attempts

### Login Endpoints

#### Student Login
- **URL**: `/student/login`
- **Guard**: `student`
- **Rate Limit**: 10 attempts per minute per IP
- **Features**:
  - Single session only (previous sessions terminated on new login)
  - Session regeneration to prevent fixation
  - Logs failed attempts
  - Redirects to voting page

#### Admin Login
- **URL**: `/admin/login`
- **Guard**: `admin`
- **Rate Limit**: 5 attempts per minute per IP (stricter)
- **Features**:
  - Single session only (enhanced security)
  - Session regeneration
  - Detailed logging of all attempts (IP, user agent)
  - Stricter validation (minimum 6 characters password)
  - Redirects to admin dashboard

### Security Implementation

```php
// Separate controllers with different security levels
StudentLoginController  // Less restrictive for usability
AdminLoginController    // Maximum security measures
```

### Access URLs

| User Type | Login URL | Dashboard URL |
|-----------|-----------|---------------|
| Student   | `/student/login` | `/voting` |
| Admin     | `/admin/login` | `/admin/dashboard` |

---

## Vote Anonymization

### How It Works

**Problem**: Traditional systems link votes directly to students, risking privacy breaches.

**Solution**: Complete vote anonymization with secure tracking.

### Votes Table Structure

```sql
CREATE TABLE votes (
  id              BIGINT AUTO_INCREMENT,
  election_id     BIGINT NOT NULL,
  position_id     BIGINT NOT NULL,
  candidate_id    BIGINT NOT NULL,
  vote_hash       VARCHAR(64) UNIQUE,      -- SHA256 hash for verification
  encrypted_voter_id VARCHAR(255),         -- Encrypted student ID
  voted_at        TIMESTAMP,
  PRIMARY KEY (id)
);
```

**Key Features:**
1. **No Direct Link**: No `student_id` foreign key in votes table
2. **Vote Hash**: Unique SHA256 hash for each vote for verification
3. **Encrypted ID**: Student ID is encrypted; only admin with key can decrypt
4. **Cannot Trace**: Impossible to determine who voted for whom without encryption key

### Voting Sessions Table

```sql
CREATE TABLE voting_sessions (
  id              BIGINT AUTO_INCREMENT,
  election_id     BIGINT NOT NULL,
  student_id      BIGINT NOT NULL,        -- Who voted
  session_token   VARCHAR(64) UNIQUE,
  ballot_number   VARCHAR(20) UNIQUE,     -- For manual counting
  status          ENUM(...),
  completed_at    TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE(election_id, student_id)         -- Prevent double voting
);
```

**Purpose**: Track WHO voted (not WHAT they voted for) to:
- Prevent double voting
- Enable manual counting verification
- Maintain election integrity

---

## Audit Trail

### Immutable Audit Log

Every action is logged in an append-only table with hash chaining to detect tampering.

```sql
CREATE TABLE vote_audit_log (
  id              BIGINT AUTO_INCREMENT,
  election_id     BIGINT NOT NULL,
  action          ENUM(...),              -- vote_cast, admin_access, etc.
  actor_type      VARCHAR(20),            -- 'student' or 'admin'
  actor_id        BIGINT,
  details         TEXT,                   -- JSON data
  previous_hash   VARCHAR(64),            -- Hash of previous entry
  entry_hash      VARCHAR(64),            -- Hash of this entry
  created_at      TIMESTAMP,
  PRIMARY KEY (id)
);
```

**Hash Chain**: Each entry contains hash of previous entry, making tampering detectable.

### Logged Actions

1. `vote_cast` - When a vote is submitted
2. `session_started` - When student starts voting
3. `session_completed` - When voting is complete
4. `session_abandoned` - If student leaves without completing
5. `suspicious_activity` - Unusual patterns detected
6. `admin_access` - Admin views results or data
7. `results_viewed` - When results are accessed

### Tampering Detection

If someone tries to modify or delete log entries:
- Hash chain breaks
- System detects mismatch
- Alert is triggered

---

## Manual Counting System

### Purpose

If the digital system fails or is compromised, manual counting provides a backup.

### How It Works

1. **Ballot Numbers**: Each student gets a unique ballot number when they vote
2. **Physical Backup**: Ballot numbers can be printed or recorded
3. **Manual Count Table**: Records manual count results

```sql
CREATE TABLE manual_count_records (
  id                    BIGINT AUTO_INCREMENT,
  election_id           BIGINT NOT NULL,
  position_id           BIGINT NOT NULL,
  candidate_id          BIGINT NOT NULL,
  manual_votes          INT DEFAULT 0,        -- Manually counted
  system_votes          INT DEFAULT 0,        -- System counted
  discrepancy           INT DEFAULT 0,        -- Difference
  counted_by_admin_id   BIGINT,
  verified_by_admin_id  BIGINT,
  notes                 TEXT,
  PRIMARY KEY (id),
  UNIQUE(election_id, position_id, candidate_id)
);
```

### Manual Counting Process

1. **During Voting**: System generates unique ballot numbers
2. **If System Fails**: Use ballot numbers to manually count
3. **Verification**: Compare manual count vs system count
4. **Discrepancy Handling**: Log and investigate differences
5. **Final Results**: Use verified count (manual or system)

### Verification Codes

Students receive a verification code after voting:

```sql
CREATE TABLE vote_verification_codes (
  id                    BIGINT AUTO_INCREMENT,
  election_id           BIGINT NOT NULL,
  voting_session_id     BIGINT NOT NULL,
  verification_code     VARCHAR(20) UNIQUE,
  total_votes_cast      INT,
  voted_timestamp       TIMESTAMP,
  verified_by_voter     BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (id)
);
```

**Benefits:**
- Students can verify their vote was counted (not who they voted for)
- Provides confidence in system
- Enables post-election verification

---

## Rate Limiting & Brute Force Protection

### Implementation

Using Laravel's `RateLimiter` facade:

#### Admin Login
```php
// Max 5 attempts per minute per IP
RateLimiter::tooManyAttempts('admin-login.' . $ip, 5)
```

#### Student Login
```php
// Max 10 attempts per minute per IP
RateLimiter::tooManyAttempts('student-login.' . $ip, 10)
```

### Protection Against

1. **Brute Force**: Limits password guessing attempts
2. **DDoS**: Prevents login endpoint flooding
3. **Credential Stuffing**: Slows down automated attacks

### Response

- After limit exceeded: Error message with countdown
- Logs suspicious activity
- Admin can review failed login attempts

---

## Session Security

### Single Session Policy

**Problem**: Multiple sessions allow account sharing and security risks.

**Solution**: Only one active session per user.

```php
// On login, invalidate all other sessions
DB::table('sessions')
    ->where('user_id', $user->id)
    ->where('id', '!=', $currentSessionId)
    ->delete();
```

### Session Regeneration

Prevents session fixation attacks:

```php
$request->session()->regenerate();
```

### Session Data

```sql
CREATE TABLE sessions (
  id            VARCHAR(255) PRIMARY KEY,
  user_id       BIGINT,
  ip_address    VARCHAR(45),
  user_agent    TEXT,
  payload       LONGTEXT,
  last_activity INT,
  INDEX(user_id),
  INDEX(last_activity)
);
```

---

## Database Security

### Password Hashing

All passwords use bcrypt (Laravel default):

```php
'password' => bcrypt('plain-text-password')
```

**Strength**: bcrypt is slow and salt-based, resistant to rainbow tables.

### SQL Injection Prevention

All queries use parameterized statements (Laravel Eloquent):

```php
// Safe
Student::where('usn', $usn)->first();

// NOT this (vulnerable)
DB::raw("SELECT * FROM students WHERE usn = '$usn'");
```

### Foreign Key Constraints

Maintains referential integrity:

```sql
CONSTRAINT votes_election_id_foreign 
  FOREIGN KEY (election_id) 
  REFERENCES elections(id) 
  ON DELETE CASCADE
```

### Encryption

Sensitive data encrypted:
- Vote-to-student link is encrypted
- Only admin with encryption key can decrypt
- Key stored securely, not in database

---

## Integrity Checking

### Automatic Integrity Checks

System performs regular checks to detect tampering:

```sql
CREATE TABLE election_integrity_checks (
  id                      BIGINT AUTO_INCREMENT,
  election_id             BIGINT NOT NULL,
  total_votes_expected    INT,          -- From voting sessions
  total_votes_counted     INT,          -- From votes table
  total_students_voted    INT,
  integrity_passed        BOOLEAN,
  issues_found            TEXT,         -- JSON
  database_hash           VARCHAR(64),  -- Current state hash
  previous_check_hash     VARCHAR(64),  -- Previous check hash
  check_timestamp         TIMESTAMP,
  PRIMARY KEY (id)
);
```

### What Gets Checked

1. **Vote Count Consistency**: votes table count matches sessions count
2. **No Double Voting**: Each student has max one session per election
3. **Hash Verification**: Current hash matches expected hash
4. **Audit Log Integrity**: Hash chain is unbroken
5. **Orphaned Records**: No votes without corresponding sessions

### When Checks Run

- **Before election starts**: Verify system integrity
- **During election**: Hourly automatic checks
- **After election**: Final comprehensive check
- **On-demand**: Admin can trigger manual check

### Response to Failed Check

1. Alert admin immediately
2. Log detailed error information
3. Optionally pause voting (if during election)
4. Require admin investigation before continuing

---

## Best Practices for Admins

### Before Election

- [ ] Change default admin password
- [ ] Test login from different devices
- [ ] Verify all students can access student login
- [ ] Run integrity check
- [ ] Backup database
- [ ] Test manual counting process

### During Election

- [ ] Monitor audit logs regularly
- [ ] Check for suspicious activity
- [ ] Run integrity checks hourly
- [ ] Keep backup admin account
- [ ] Monitor server resources

### After Election

- [ ] Run final integrity check
- [ ] Compare manual count (if performed)
- [ ] Generate and backup results
- [ ] Archive election data
- [ ] Review audit logs for anomalies

---

## Security Incident Response

### If Compromise Suspected

1. **Immediate Actions**:
   - Pause election if still active
   - Lock admin access
   - Take database backup
   - Review audit logs

2. **Investigation**:
   - Check integrity check results
   - Review failed login attempts
   - Analyze audit log hash chain
   - Compare vote counts with manual records

3. **Resolution**:
   - If system compromised: Use manual count
   - If no compromise found: Resume election
   - Document incident
   - Implement additional security measures

---

## Security Checklist

### System Security
- [x] Separate admin/student login pages
- [x] Rate limiting on all login endpoints
- [x] Single session per user
- [x] Session regeneration on login
- [x] Bcrypt password hashing
- [x] SQL injection protection (parameterized queries)

### Vote Security
- [x] Anonymous voting (no direct student-vote link)
- [x] Encrypted voter ID
- [x] Unique vote hash for verification
- [x] Prevent double voting (unique constraint)

### Audit & Logging
- [x] Immutable audit log
- [x] Hash chain for tamper detection
- [x] Log all critical actions
- [x] Separate admin vs student logs

### Manual Backup
- [x] Ballot number system
- [x] Manual count recording
- [x] Discrepancy tracking
- [x] Verification codes for voters

### Integrity
- [x] Automatic integrity checks
- [x] Hash verification
- [x] Vote count consistency checks
- [x] Audit log validation

---

## URLs Reference

### Public URLs
- `/` - Redirects to student login
- `/student/login` - Student login page

### Hidden Admin URLs (Don't share publicly)
- `/admin/login` - Admin login page
- `/admin/dashboard` - Admin dashboard

### Student URLs (Requires Authentication)
- `/voting` - Voting page
- `/voting/success` - Success page

### Admin URLs (Requires Admin Authentication)
- `/admin/dashboard` - Overview
- `/admin/elections` - Election management
- `/admin/results` - View results
- `/admin/users` - User management

---

## Additional Security Recommendations

### Server Level

1. **HTTPS Only**: Always use SSL/TLS
2. **Firewall**: Restrict unnecessary ports
3. **Regular Updates**: Keep OS and software updated
4. **Backup**: Daily automated backups
5. **Monitoring**: Set up intrusion detection

### Application Level

1. **Environment Variables**: Keep `.env` secure
2. **Debug Mode**: Turn off in production (`APP_DEBUG=false`)
3. **Logs**: Rotate logs regularly
4. **Dependencies**: Keep Laravel and packages updated
5. **2FA**: Consider implementing for admin accounts

### Network Level

1. **IP Whitelist**: Optionally restrict admin access to specific IPs
2. **VPN**: Require VPN for admin access
3. **CDN**: Use CDN for DDoS protection
4. **Load Balancer**: Distribute traffic for high-volume elections

---

## Conclusion

This voting system implements multiple layers of security:

1. **Separation of Concerns**: Different login pages for different user types
2. **Vote Anonymization**: Impossible to trace votes without encryption key
3. **Audit Trail**: Every action logged with tamper detection
4. **Manual Backup**: Physical counting possible if digital system fails
5. **Integrity Checking**: Automatic detection of anomalies
6. **Rate Limiting**: Protection against brute force attacks
7. **Session Security**: Single session, regeneration, proper timeout

**Result**: A secure, trustworthy voting system that's difficult to hack and can be manually verified.

---

**Last Updated**: 2026-02-05  
**Version**: 1.0
