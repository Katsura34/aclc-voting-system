# Security Documentation

This document outlines all security measures implemented in the ACLC Voting System.

## Table of Contents
1. [Authentication & Authorization](#authentication--authorization)
2. [Rate Limiting](#rate-limiting)
3. [CSRF Protection](#csrf-protection)
4. [XSS Protection](#xss-protection)
5. [Password Security](#password-security)
6. [Audit Logging](#audit-logging)
7. [Session Management](#session-management)
8. [HTTPS/SSL Configuration](#httpsssl-configuration)
9. [Access Control](#access-control)

---

## Authentication & Authorization

### Role-Based Access Control (RBAC)

The system implements a two-tier role system:
- **Admin**: Full access to all system features
- **Student**: Limited to voting functionality only

#### Middleware Protection

All admin routes are protected by two middleware layers:
1. `auth` - Ensures user is authenticated
2. `admin` - Ensures user has admin role (user_type === 'admin')

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // All admin routes here
});
```

#### Admin Middleware Implementation

Location: `app/Http/Middleware/EnsureUserIsAdmin.php`

- Checks if user is authenticated
- Verifies user has `user_type` set to 'admin'
- Returns 403 Forbidden for non-admin users
- Redirects to login for unauthenticated users

### Authorization Checks

Every admin controller method should verify:
1. User is authenticated
2. User has admin role
3. User has permission for the specific action

---

## Rate Limiting

### Login Rate Limiting

**Implementation**: `app/Http/Controllers/Auth/LoginController.php`

- **Limit**: 5 login attempts per minute
- **Key**: Combination of username (USN) and IP address
- **Behavior**:
  - After 5 failed attempts, user must wait 60 seconds
  - Successful login clears the rate limiter
  - Clear error message shows remaining seconds

**Example**:
```php
$key = Str::lower($request->input('usn')).'|'.$request->ip();

if (RateLimiter::tooManyAttempts($key, 5)) {
    $seconds = RateLimiter::availableIn($key);
    // Show error message
}
```

---

## CSRF Protection

### Built-in Laravel CSRF

Laravel's CSRF protection is enabled by default on all POST, PUT, PATCH, and DELETE requests.

**Verification**:
- All forms include `@csrf` directive
- AJAX requests include CSRF token in headers
- Middleware: `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken`

**Forms with CSRF**:
```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    <!-- form fields -->
</form>
```

---

## XSS Protection

### Output Escaping

All user-generated content is escaped using Blade's `{{ }}` syntax:

```blade
{{ $user->name }}  // Automatically escaped
{{ $election->title }}  // Automatically escaped
```

### Never Use Unescaped Output

The system does NOT use `{!! !!}` for user-generated content to prevent XSS attacks.

### Input Validation

All user inputs are validated using Laravel's validation rules before being stored.

---

## Password Security

### Bcrypt Hashing

Passwords are hashed using bcrypt with Laravel's default configuration.

**Implementation**:
```php
// In User model
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}

// In controllers
Hash::make($password);  // Always use Hash::make()
```

### Password Requirements

- Minimum 8 characters
- Must be confirmed (password_confirmation field)
- Never stored in plain text
- Uses bcrypt with cost factor of 12 (default)

**Verification**:
```bash
# Check .env file
BCRYPT_ROUNDS=12
```

---

## Audit Logging

### Vote Audit Logs

Every vote cast is logged with:
- User ID
- Election ID
- Position ID
- Candidate ID (or null for abstain)
- Action type (vote_cast, vote_abstain, vote_reset)
- IP address
- User agent
- Timestamp

**Table**: `vote_audit_logs`

### Admin Audit Logs

All admin actions are logged with:
- Admin user ID
- Action performed (create, update, delete, reset_vote, etc.)
- Model type and ID affected
- Old and new values (for updates)
- Description
- IP address
- User agent
- Timestamp

**Table**: `admin_audit_logs`

**Usage in Controllers**:
```php
use App\Traits\LogsAdminActions;

class UserController extends Controller
{
    use LogsAdminActions;

    public function store(Request $request)
    {
        // ... create user ...
        $this->logAdminAction(
            'create',
            "Created user: {$user->name}",
            User::class,
            $user->id,
            null,
            $validated
        );
    }
}
```

---

## Session Management

### Session Configuration

- **Driver**: Database (secure and scalable)
- **Lifetime**: 120 minutes
- **Regeneration**: On login (prevents session fixation)
- **Invalidation**: On logout

**Session Security**:
```php
// On login
$request->session()->regenerate();

// On logout
$request->session()->invalidate();
$request->session()->regenerateToken();
```

### Session Settings

Check `.env` file:
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

---

## HTTPS/SSL Configuration

### Production Requirements

For production deployment, HTTPS/SSL MUST be configured:

#### 1. Obtain SSL Certificate

**Options**:
- Let's Encrypt (free, recommended)
- Commercial SSL provider
- Cloud provider SSL (AWS Certificate Manager, etc.)

#### 2. Web Server Configuration

**For Apache** (`/etc/apache2/sites-available/voting-ssl.conf`):
```apache
<VirtualHost *:443>
    ServerName voting.yourdomain.com
    DocumentRoot /path/to/aclc-voting-system/public

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

**For Nginx** (`/etc/nginx/sites-available/voting`):
```nginx
server {
    listen 443 ssl http2;
    server_name voting.yourdomain.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    root /path/to/aclc-voting-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name voting.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

#### 3. Laravel Configuration

Update `.env`:
```env
APP_URL=https://voting.yourdomain.com
SESSION_SECURE_COOKIE=true
```

Update `config/session.php`:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'lax',
```

#### 4. Force HTTPS (Optional)

Add to `app/Providers/AppServiceProvider.php`:
```php
public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

---

## Access Control

### Route Protection Summary

| Route Pattern | Auth Required | Admin Required | Middleware |
|--------------|---------------|----------------|------------|
| `/login` | No | No | - |
| `/logout` | Yes | No | auth |
| `/voting/*` | Yes | No | auth |
| `/admin/*` | Yes | Yes | auth, admin |

### Preventing Unauthorized Access

1. **Authentication Check**: All protected routes require login
2. **Role Check**: Admin routes check `user_type === 'admin'`
3. **Ownership Check**: Users can only modify their own data (where applicable)
4. **CSRF Check**: All state-changing requests require valid CSRF token
5. **Rate Limiting**: Login attempts are rate-limited

### Testing Access Control

Run the test suite to verify access control:
```bash
php artisan test --filter=RBACTest
```

---

## Security Checklist

### Pre-Deployment

- [ ] All admin routes have `admin` middleware
- [ ] CSRF protection enabled on all forms
- [ ] All user inputs validated
- [ ] All outputs properly escaped
- [ ] Password hashing using bcrypt
- [ ] Rate limiting enabled on login
- [ ] Session regeneration on login/logout
- [ ] Audit logging for all critical actions
- [ ] HTTPS/SSL certificate installed
- [ ] Security headers configured
- [ ] Database credentials secured
- [ ] `.env` file not in version control
- [ ] Error messages don't leak sensitive info
- [ ] File upload validation (if applicable)
- [ ] SQL injection prevention (using Eloquent)

### Post-Deployment

- [ ] Regular security audits
- [ ] Monitor audit logs for suspicious activity
- [ ] Keep Laravel and dependencies updated
- [ ] Regular database backups
- [ ] Monitor failed login attempts
- [ ] Review admin action logs periodically
- [ ] Test rate limiting functionality
- [ ] Verify HTTPS is enforced
- [ ] Check for outdated dependencies: `composer outdated`

---

## Incident Response

If a security incident is detected:

1. **Identify**: Review audit logs to determine scope
2. **Contain**: Reset affected user passwords, invalidate sessions
3. **Investigate**: Check `vote_audit_logs` and `admin_audit_logs`
4. **Remediate**: Fix vulnerability, update code
5. **Document**: Record incident and response
6. **Notify**: Inform affected users if necessary

---

## Security Contacts

For security issues or concerns:
- Report to system administrator
- Do not disclose publicly until fixed
- Include detailed reproduction steps

---

## Compliance

This system implements security measures consistent with:
- OWASP Top 10 protection
- Laravel security best practices
- Academic institution data protection standards

---

## Updates

This document should be updated whenever:
- New security features are added
- Security vulnerabilities are fixed
- Authentication/authorization logic changes
- Audit logging requirements change

Last Updated: 2025-11-15
