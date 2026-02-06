# User CSV Import Feature

This document describes how to use the bulk CSV import feature for users.

## CSV Format

The CSV file must have the following columns in this exact order:

1. **usn** - Unique Student Number (required)
2. **lastname** - Student's last name (required)
3. **firstname** - Student's first name (required)
4. **strand** - Academic strand (optional: STEM, ABM, HUMSS, GAS, etc.)
5. **year** - Year level (optional: 1st Year, 2nd Year, 3rd Year, 4th Year)
6. **gender** - Gender (optional: Male, Female, Other)
7. **password** - Initial password for the account (required)

## Example CSV

```csv
usn,lastname,firstname,strand,year,gender,password
2024-001,Doe,John,STEM,1st Year,Male,password123
2024-002,Smith,Jane,ABM,2nd Year,Female,password123
2024-003,Johnson,Michael,HUMSS,3rd Year,Male,password123
```

## How to Import

1. Navigate to the **Manage Students** page in the admin panel
2. Click the **Download Template** button to get a sample CSV file
3. Fill in your student data following the template format
4. Click the **Import CSV** button
5. Select your CSV file
6. Click **Import Users**

## Important Notes

- The first row must be the header row with column names
- USN must be unique - duplicate USNs will be skipped
- Email addresses are automatically generated from USN: `{usn}@aclc.edu.ph`
- All imported users will be created as students by default
- Empty rows are automatically skipped
- The system will show a summary of successful imports and any errors

## Sample File

A sample CSV file is available at: `storage/app/sample_users.csv`

## Field Descriptions

### Required Fields
- **usn**: Must be unique across all users
- **lastname**: Student's family name
- **firstname**: Student's given name  
- **password**: Minimum 8 characters recommended

### Optional Fields
- **strand**: Academic program/strand
- **year**: Current year level
- **gender**: Must be one of: Male, Female, or Other (if provided)

## Error Handling

The import process will:
- Skip rows with missing required fields
- Skip rows with duplicate USNs
- Skip rows with invalid data
- Show detailed error messages for failed imports
- Continue processing remaining rows even if some fail
