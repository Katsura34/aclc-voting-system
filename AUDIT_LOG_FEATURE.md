# Audit Log Feature Documentation

## Overview
The audit log feature provides comprehensive tracking of all votes cast in the voting system. This allows administrators to:
- View detailed records of who voted for which candidate
- Export voting records for manual counting and verification
- Print voter lists in a formatted layout for offline use
- Search and filter audit logs by election, position, or voter

## Features

### 1. Automatic Vote Logging
Every time a student casts a vote, the system automatically creates an audit log entry containing:
- Student's USN (University Student Number)
- Student's full name
- Position voted for
- Candidate selected
- IP address of the voter
- User agent (browser information)
- Exact timestamp of the vote

### 2. Admin Interface
Administrators can access the audit logs through the "Audit Logs" menu item in the admin sidebar.

#### View Audit Logs
- **URL**: `/admin/audit-logs`
- **Features**:
  - Select an election to view its audit logs
  - Search by USN, student name, or candidate name
  - Paginated view showing 50 records per page
  - Statistics showing total votes, unique voters, and positions

#### Export to CSV
- **URL**: `/admin/audit-logs/export?election_id={id}`
- **Output**: CSV file containing all audit log entries for an election
- **Columns**: Timestamp, Student USN, Student Name, Position, Candidate, IP Address
- **Use Case**: Import into Excel or other tools for manual counting and verification

#### Print View
- **URL**: `/admin/audit-logs/print?election_id={id}`
- **Output**: A4-formatted printable page
- **Layout**: 
  - Header with election title and generation date
  - Summary statistics (total voters, positions, vote records)
  - Grouped by position for easy manual counting
  - Each position shows all votes in chronological order
- **Use Case**: Print for offline record-keeping or manual verification

## Database Schema

### audit_logs Table
```sql
id                  - Primary key
user_id             - Foreign key to users table
election_id         - Foreign key to elections table
position_id         - Foreign key to positions table
candidate_id        - Foreign key to candidates table (nullable)
action_type         - Type of action (default: 'vote_cast')
user_usn            - Cached student USN
user_name           - Cached student name
candidate_name      - Cached candidate name
position_name       - Cached position name
ip_address          - IP address of voter
user_agent          - Browser user agent
voted_at            - Timestamp of vote
created_at          - Record creation timestamp
updated_at          - Record update timestamp
```

**Indexes:**
- `election_id` - For fast filtering by election
- `user_id` - For fast filtering by user
- `(election_id, voted_at)` - For chronological queries within an election

## Data Integrity

### Transaction Safety
Audit log entries are created within the same database transaction as vote records. This ensures:
- If vote recording fails, audit log is not created
- If audit log creation fails, vote is not recorded
- Data consistency is maintained at all times

### Cached Data
The audit log stores denormalized copies of user and candidate names to ensure:
- Historical accuracy even if names are later changed
- Fast queries without joins
- Data availability even if related records are deleted

## Security Considerations

### Sensitive Information
Audit logs contain sensitive voter information and should be:
- Only accessible to administrators
- Handled securely when exported or printed
- Protected by proper access controls
- Not shared publicly

### IP Address Logging
IP addresses are logged for:
- Detecting potential voting irregularities
- Identifying suspicious activity
- Supporting system audits

## Use Cases

### 1. Error Recovery
If the system experiences an error during vote counting, administrators can:
1. Access the audit logs for the affected election
2. Export to CSV
3. Perform manual counting
4. Verify results against automated counts

### 2. Dispute Resolution
If a voter claims their vote wasn't recorded:
1. Search audit logs by voter's USN
2. Verify if and when they voted
3. Check which candidates they selected

### 3. System Audit
For transparency and accountability:
1. Print audit logs after election closes
2. Store physical copies as official records
3. Compare against electronic results

### 4. Compliance
Meet regulatory or institutional requirements for:
- Vote tracking
- Audit trails
- Record retention
- Transparency

## Future Enhancements

Possible future improvements:
- Add more action types (vote_deleted, vote_modified, etc.)
- Include admin actions in audit logs
- Add filters by date range
- Implement audit log retention policies
- Add digital signatures for tamper-evident logs
