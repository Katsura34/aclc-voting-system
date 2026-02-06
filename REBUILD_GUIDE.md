# ACLC Voting System - Rebuild Guide

## Overview

This guide provides step-by-step instructions to rebuild the ACLC Voting System from scratch. Whether you're setting up a new instance, migrating to a new server, or starting fresh, this guide has you covered.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [System Requirements](#system-requirements)
3. [Installation Steps](#installation-steps)
4. [Database Setup](#database-setup)
5. [Configuration](#configuration)
6. [Initial Data Setup](#initial-data-setup)
7. [Testing](#testing)
8. [Deployment](#deployment)

---

## Prerequisites

Before you begin, ensure you have:

- Basic knowledge of Laravel framework
- Access to a server with terminal/SSH
- Database server access (MySQL/MariaDB)
- Web server (Apache/Nginx)
- Git installed

---

## System Requirements

### Server Requirements

- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Composer**: 2.x

### PHP Extensions Required

```bash
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
```

### Disk Space

- Minimum: 500 MB
- Recommended: 2 GB (for logs, uploads, etc.)

---

## Installation Steps

### Step 1: Clone the Repository

```bash
# Clone the repository
git clone https://github.com/Katsura34/aclc-voting-system.git
cd aclc-voting-system
```

Or if you're starting completely fresh, create a new Laravel project:

```bash
composer create-project laravel/laravel aclc-voting-system
cd aclc-voting-system
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Environment Variables

Edit `.env` file with your settings:

```env
APP_NAME="ACLC Voting System"
APP_ENV=production
APP_KEY=base64:... # Generated in previous step
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aclc_voting
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration (for password resets)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@aclc-voting.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Database Setup

### Option 1: Using Laravel Migrations (Recommended)

This is the cleanest approach and ensures all relationships are properly set up.

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE aclc_voting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed --class=VotingSystemSeeder
```

### Option 2: Using SQL Schema File

If you prefer to import the schema directly:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE aclc_voting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p aclc_voting < database/schema.sql
```

### Verify Database Setup

```bash
# List all tables
php artisan db:table --list

# Or using MySQL
mysql -u root -p aclc_voting -e "SHOW TABLES;"
```

You should see these tables:
- admins
- cache
- cache_locks
- candidates
- elections
- parties
- password_reset_tokens
- positions
- sessions
- students
- votes
- voting_records

---

## Configuration

### File Permissions

Set proper permissions for Laravel:

```bash
# Storage and cache directories
chmod -R 775 storage bootstrap/cache

# Set ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data storage bootstrap/cache

# For uploads
mkdir -p storage/app/public/candidates
chmod -R 775 storage/app/public
```

### Create Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public` for file uploads.

### Web Server Configuration

#### Apache (.htaccess)

Laravel includes an `.htaccess` file in the public directory. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configure your virtual host to point to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/aclc-voting-system/public

    <Directory /path/to/aclc-voting-system/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/aclc-voting-error.log
    CustomLog ${APACHE_LOG_DIR}/aclc-voting-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/aclc-voting-system/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Initial Data Setup

### Create Admin Account

You can create an admin account in multiple ways:

#### Method 1: Using Tinker

```bash
php artisan tinker
```

Then in the Tinker console:

```php
App\Models\Admin::create([
    'username' => 'admin',
    'name' => 'System Administrator',
    'password' => bcrypt('admin123')
]);
```

#### Method 2: Using Seeder

Create a custom seeder:

```bash
php artisan make:seeder AdminSeeder
```

Edit `database/seeders/AdminSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username' => 'admin',
            'name' => 'System Administrator',
            'password' => bcrypt('admin123'), // Change this!
        ]);
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=AdminSeeder
```

### Import Student Data

If you have student data in CSV format:

1. Login as admin
2. Navigate to Admin Dashboard → Students
3. Use the CSV import feature

CSV format should be:
```csv
usn,name,email,password
2021-001,John Doe,john@example.com,password123
2021-002,Jane Smith,jane@example.com,password456
```

### Set Up Election

1. **Create Election**
   - Login as admin
   - Navigate to Elections → Create New
   - Fill in details (title, dates, etc.)
   - Mark as active

2. **Create Positions**
   - Under the election, create positions
   - Set order and max_winners
   - Common positions:
     - President (max_winners: 1)
     - Vice President (max_winners: 1)
     - Secretary (max_winners: 1)
     - Treasurer (max_winners: 1)
     - Representatives (max_winners: 3+)

3. **Create Parties (Optional)**
   - Navigate to Parties → Create New
   - Add name, acronym, color, logo
   - Example: Unity Party (UP), Progress Alliance (PA)

4. **Add Candidates**
   - For each position, add candidates
   - Include: name, party, course, year, bio, photo
   - Independent candidates don't need party affiliation

---

## Testing

### Test Database Connection

```bash
php artisan migrate:status
```

### Test Application

```bash
# Start development server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

### Run Automated Tests

```bash
# Run PHPUnit tests
php artisan test

# Or
vendor/bin/phpunit
```

### Manual Testing Checklist

- [ ] Admin login works
- [ ] Admin can create elections
- [ ] Admin can add positions
- [ ] Admin can add candidates
- [ ] Admin can import students via CSV
- [ ] Students can login
- [ ] Students can view ballot
- [ ] Students can vote
- [ ] Students cannot vote twice
- [ ] Results display correctly (after election)

---

## Build Frontend Assets

### Development

```bash
npm run dev
```

### Production

```bash
npm run build
```

This compiles and minifies assets for production.

---

## Optimization for Production

### 1. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Enable OPcache

In your `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
```

### 4. Set Up Queue Worker (Optional)

For background jobs:

```bash
# Install supervisor
sudo apt install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/aclc-voting-worker.conf
```

Add:

```ini
[program:aclc-voting-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/aclc-voting-system/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/aclc-voting-system/storage/logs/worker.log
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start aclc-voting-worker:*
```

---

## Security Hardening

### 1. Change Default Credentials

- Change admin password immediately
- Use strong passwords (12+ characters, mixed case, numbers, symbols)

### 2. Enable HTTPS

```bash
# Install Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d your-domain.com
```

### 3. Environment File Security

```bash
# Ensure .env is not accessible
chmod 600 .env

# Never commit .env to git
# It should be in .gitignore
```

### 4. Database Security

- Use strong database passwords
- Restrict database user permissions
- Don't use root for application database access

### 5. Firewall Configuration

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable
```

---

## Backup Strategy

### Automated Daily Backup Script

Create `/usr/local/bin/backup-aclc-voting.sh`:

```bash
#!/bin/bash

# Configuration
APP_DIR="/path/to/aclc-voting-system"
BACKUP_DIR="/backups/aclc-voting"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u username -ppassword aclc_voting | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup uploaded files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $APP_DIR/storage/app/public .

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Make it executable and add to cron:

```bash
chmod +x /usr/local/bin/backup-aclc-voting.sh

# Add to crontab (daily at 2 AM)
crontab -e
0 2 * * * /usr/local/bin/backup-aclc-voting.sh
```

---

## Troubleshooting

### Common Issues

#### 1. "500 Internal Server Error"

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Ensure proper permissions
chmod -R 775 storage bootstrap/cache
```

#### 2. "Database connection refused"

**Solution:**
- Check DB credentials in `.env`
- Ensure MySQL is running: `sudo systemctl status mysql`
- Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`

#### 3. "Class not found" errors

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
```

#### 4. "Mix manifest does not exist"

**Solution:**
```bash
npm install
npm run build
```

#### 5. Voting system not working

**Solution:**
- Check if election is active
- Verify student hasn't already voted
- Check browser console for JavaScript errors
- Review `storage/logs/laravel.log`

---

## Maintenance

### Regular Tasks

**Daily:**
- Check error logs
- Monitor disk space
- Verify backups completed

**Weekly:**
- Review system performance
- Check for Laravel/dependency updates
- Test backup restoration

**Before Each Election:**
- Full system backup
- Test all functionality
- Verify student list is current
- Check server resources

**After Each Election:**
- Archive election data
- Generate reports
- Backup results
- Clean up temporary data

---

## Monitoring

### Log Locations

- **Laravel Logs**: `storage/logs/laravel.log`
- **Apache Logs**: `/var/log/apache2/`
- **Nginx Logs**: `/var/log/nginx/`
- **MySQL Logs**: `/var/log/mysql/`

### Monitoring Tools

Consider installing:
- **Laravel Telescope** (development): For debugging
- **Laravel Horizon** (if using Redis): For queue monitoring
- **New Relic / Datadog**: For production monitoring

---

## Upgrade Path

To upgrade from an older version:

```bash
# Backup first!
mysqldump -u username -p aclc_voting > backup.sql

# Pull latest code
git pull origin main

# Update dependencies
composer install
npm install

# Run new migrations
php artisan migrate

# Rebuild assets
npm run build

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Support and Resources

### Documentation
- Laravel: https://laravel.com/docs
- Database Schema: See `DATABASE.md`
- Deployment: See `DEPLOYMENT_GUIDE.md`

### Getting Help
- Check `storage/logs/laravel.log` for errors
- Review this guide
- Contact system administrator

---

## Checklist for Production Deployment

- [ ] Server meets requirements
- [ ] PHP extensions installed
- [ ] Composer installed
- [ ] Node.js/NPM installed
- [ ] Repository cloned
- [ ] Dependencies installed
- [ ] `.env` configured
- [ ] Database created
- [ ] Migrations run
- [ ] Admin account created
- [ ] File permissions set
- [ ] Storage linked
- [ ] Web server configured
- [ ] Assets built
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] System tested
- [ ] Documentation reviewed

---

**Last Updated**: 2026-02-05  
**Version**: 1.0
