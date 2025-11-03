# Single Session Per Account Feature

## Overview
This feature ensures that only one active session exists per user account at any time. When a user logs in from a new device or browser, all previous sessions are automatically invalidated.

## Implementation

### 1. Sessions Table Migration
**File:** `database/migrations/2025_11_03_000001_create_sessions_table.php`

Creates the sessions table with:
- `id`: Session identifier (primary key)
- `user_id`: Foreign key to users table (for tracking user sessions)
- `ip_address`: Client IP address
- `user_agent`: Browser information
- `payload`: Session data
- `last_activity`: Timestamp for session expiry

### 2. Login Controller Enhancement
**File:** `app/Http/Controllers/Auth/LoginController.php`

**Changes:**
- Added session invalidation logic in the `login()` method
- When user successfully authenticates:
  1. Gets current session ID
  2. Deletes all other sessions for this user from database
  3. Keeps only the current session active
  4. Logs the action for audit purposes

**Code:**
```php
// Delete all other sessions for this user
DB::table('sessions')
    ->where('user_id', $user->id)
    ->where('id', '!=', $currentSessionId)
    ->delete();
```

### 3. Session Validation Middleware
**File:** `app/Http/Middleware/ValidateSession.php`

**Purpose:** Validates that the current session still exists in the database for authenticated users.

**How it works:**
- Runs on every request for authenticated users
- Checks if current session ID exists in database for the logged-in user
- If session doesn't exist (was invalidated by new login):
  - Logs out the user
  - Invalidates the session
  - Redirects to login with message

**Middleware Registration:**
- Added to web middleware group in `bootstrap/app.php`
- Runs automatically for all web routes

## User Experience

### Scenario 1: User logs in from Device A
1. User successfully logs in
2. Session created in database
3. User can access the system normally

### Scenario 2: Same user logs in from Device B
1. User logs in successfully from Device B
2. System creates new session for Device B
3. System deletes Device A's session from database
4. Device A's next request fails session validation
5. Device A user is logged out with message: "Your session has been terminated because you logged in from another device or browser."

### Scenario 3: User logs out normally
1. User clicks logout
2. Session is invalidated and removed
3. User redirected to login page

## Security Benefits

1. **Account Protection:** Prevents unauthorized concurrent access
2. **Shared Credential Detection:** Alerts users when account is accessed elsewhere
3. **Session Management:** Clear tracking of active sessions per user
4. **Automatic Cleanup:** Old sessions are automatically removed on new login

## Database Changes Required

Run the migration to create the sessions table:
```bash
php artisan migrate
```

## Configuration

The session configuration is in `config/session.php`:
- Driver: `database` (required for this feature)
- Lifetime: 120 minutes (2 hours)
- Table: `sessions`

## Logging

All login events with session invalidation are logged:
```
User logged in (previous sessions invalidated)
- user_id: [user ID]
- usn: [user student number]
```

## Testing

### Test Case 1: Single Session Enforcement
1. Log in from Browser A
2. Log in from Browser B with same credentials
3. Verify Browser A is logged out on next request
4. Verify Browser B remains logged in

### Test Case 2: Error Message Display
1. After being logged out by new session
2. Verify error message is displayed
3. Verify message is user-friendly

### Test Case 3: Normal Logout
1. Log in normally
2. Click logout
3. Verify clean logout without errors

## Rollback

If this feature needs to be disabled:

1. Remove middleware from `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    // Comment out or remove this line
    // $middleware->appendToGroup('web', \App\Http\Middleware\ValidateSession::class);
})
```

2. Revert LoginController changes to remove session invalidation logic

3. (Optional) Drop sessions table if not needed:
```bash
php artisan migrate:rollback
```

## Limitations

- Only works with database session driver
- Requires sessions table in database
- Users on slow connections might experience slight delay during login

## Future Enhancements

Potential improvements:
1. Show list of active sessions to users
2. Allow users to manually terminate specific sessions
3. Add "Remember this device" option to trust certain devices
4. Send email notifications when new login detected
5. Add session activity tracking (last activity, device info)

## Support

For issues with single session feature:
1. Check `storage/logs/laravel.log` for session-related errors
2. Verify sessions table exists and is populated
3. Confirm session driver is set to `database` in `.env`
4. Check that middleware is properly registered
