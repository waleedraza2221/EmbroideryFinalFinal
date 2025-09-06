# Supabase Database Configuration Guide

This guide covers configuring your Laravel Embroidery Management System to use Supabase as the database backend.

## 🎯 Why Supabase?

- **PostgreSQL-based**: More robust than MySQL for complex applications
- **Real-time features**: Built-in subscriptions and live updates
- **Auto-generated APIs**: REST and GraphQL APIs out of the box
- **Built-in authentication**: User management with social logins
- **Edge functions**: Serverless functions for custom logic
- **Free tier**: Generous limits for development and small production apps

## 🚀 Supabase Setup

### Step 1: Create Supabase Project

1. **Go to Supabase Dashboard**: https://supabase.com/dashboard
2. **Sign up/Login** with GitHub or email
3. **Create New Project**:
   - Project name: `embroidery-management`
   - Database password: Generate strong password
   - Region: Choose closest to your VPS location
   - Plan: Start with Free tier

### Step 2: Get Database Credentials

In your Supabase project dashboard:

1. **Go to Settings** → **Database**
2. **Copy Connection Details**:
   - Host: `db.xxx.supabase.co`
   - Database: `postgres`
   - Port: `5432`
   - User: `postgres`
   - Password: Your project password

3. **Get API Keys** (Settings → API):
   - Project URL: `https://xxx.supabase.co`
   - Anon key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
   - Service role key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`

### Step 3: Configure Laravel for PostgreSQL

#### Install PostgreSQL PHP Extension

**For AlmaLinux 8 cPanel:**
```bash
# SSH into your VPS as root
ssh root@your-vps-ip

# Install PostgreSQL PHP extension
dnf install -y ea-php82-php-pgsql ea-php82-php-pdo_pgsql

# Restart PHP-FPM
systemctl restart ea-php82-php-fpm
```

**For Ubuntu/Debian VPS:**
```bash
sudo apt update
sudo apt install -y php8.2-pgsql
sudo systemctl restart php8.2-fpm
```

#### Update Laravel Configuration

1. **Install Laravel PostgreSQL Support** (if not already included):
```bash
# In your project directory
composer require doctrine/dbal
```

2. **Update `.env` file**:
```env
APP_NAME="Embroidery Management System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Supabase Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db.xxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

# Supabase API Configuration
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_SERVICE_KEY=your_service_role_key

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Embroidery Converter Settings
LIBEMBROIDERY_PATH=/usr/local/bin/libembroidery-convert
CONVERSION_TIMEOUT=300

# Session and Cache (using database)
SESSION_DRIVER=database
CACHE_DRIVER=database
```

### Step 4: Database Migration

#### Run Laravel Migrations:
```bash
# SSH into your application directory
cd /home/username/public_html

# Test database connection
php artisan tinker
# In tinker: DB::connection()->getPdo();
# Should return PDO object without errors

# Run migrations
php artisan migrate

# If you have seeders:
php artisan db:seed
```

#### Import Existing Data (if migrating):
```bash
# Export from your current MySQL database
mysqldump -u user -p database_name > export.sql

# Convert MySQL dump to PostgreSQL format (use online converter or pgloader)
# Then import to Supabase via dashboard or psql
```

### Step 5: Supabase-Specific Features (Optional)

#### Enable Row Level Security (RLS):
```sql
-- In Supabase SQL Editor
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Create policy for user data access
CREATE POLICY "Users can view own data" ON users
FOR SELECT USING (auth.uid() = id::text);

CREATE POLICY "Users can update own data" ON users
FOR UPDATE USING (auth.uid() = id::text);
```

#### Real-time Subscriptions:
```sql
-- Enable real-time for tables (in Supabase dashboard)
-- Go to Database → Replication → Enable for desired tables
```

## 🔧 Laravel Supabase Integration

### Install Supabase PHP Client (Optional):
```bash
composer require supabase/supabase-php
```

### Create Supabase Service Provider:
```php
<?php
// app/Services/SupabaseService.php

namespace App\Services;

use Supabase\CreateClient;

class SupabaseService
{
    private $supabase;

    public function __construct()
    {
        $this->supabase = CreateClient::create(
            config('services.supabase.url'),
            config('services.supabase.key')
        );
    }

    public function getClient()
    {
        return $this->supabase;
    }

    // Example: Upload file to Supabase Storage
    public function uploadFile($bucket, $path, $file)
    {
        return $this->supabase->storage
            ->from($bucket)
            ->upload($path, $file);
    }

    // Example: Real-time subscription
    public function subscribeToTable($table, $callback)
    {
        return $this->supabase
            ->from($table)
            ->on('*', $callback)
            ->subscribe();
    }
}
```

### Add Supabase Config:
```php
<?php
// config/services.php

return [
    // ... other services
    
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_ANON_KEY'),
        'service_key' => env('SUPABASE_SERVICE_KEY'),
    ],
];
```

## 📊 Database Performance Optimization

### Indexing Strategy:
```sql
-- In Supabase SQL Editor, create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_created_at ON orders(created_at);
```

### Connection Pooling:
Supabase automatically handles connection pooling, but you can optimize:

```env
# Add to .env for better connection management
DB_POOL_MIN=2
DB_POOL_MAX=10
```

## 🔄 Backup Strategy

### Automated Backups:
1. **Supabase Pro Plan**: Automated daily backups
2. **Free Plan**: Manual backups via dashboard

### Manual Backup Script:
```bash
#!/bin/bash
# backup-supabase.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/backups"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup using pg_dump (install postgresql-client first)
pg_dump "postgresql://postgres:password@db.xxx.supabase.co:5432/postgres" > $BACKUP_DIR/supabase_backup_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "supabase_backup_*.sql" -mtime +7 -delete

echo "Backup completed: supabase_backup_$DATE.sql"
```

Add to crontab for daily backups:
```bash
crontab -e
# Add: 0 2 * * * /home/backup-supabase.sh
```

## 🔒 Security Best Practices

### Database Security:
- **Enable RLS**: Row Level Security for sensitive tables
- **API Keys**: Keep service role key secure, use anon key for frontend
- **Network Restrictions**: Configure allowed IP addresses in Supabase dashboard
- **SSL Connections**: Always use SSL (enabled by default)

### Laravel Security:
```env
# Additional security settings for production
DB_SSLMODE=require
DB_SSLCERT=
DB_SSLKEY=
DB_SSLROOTCERT=
```

## 📈 Monitoring and Analytics

### Supabase Dashboard:
- **Database Statistics**: Query performance and usage
- **API Analytics**: Request metrics and response times
- **Real-time Monitoring**: Active connections and subscriptions
- **Logs**: Database and API logs

### Laravel Integration:
```php
<?php
// Add to AppServiceProvider for query logging
public function boot()
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            Log::info('Query: ' . $query->sql, [
                'bindings' => $query->bindings,
                'time' => $query->time
            ]);
        });
    }
}
```

## 🚀 Deployment with Supabase

### Updated Deployment Commands:
```bash
# After uploading your Laravel app to VPS
cd /home/username/public_html

# Install dependencies including PostgreSQL support
composer install --optimize-autoloader --no-dev

# Generate app key
php artisan key:generate

# Test Supabase connection
php artisan tinker
# DB::connection()->getPdo();

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
```

## 🔧 Troubleshooting

### Common Issues:

#### 1. Connection Refused:
- Verify Supabase credentials in `.env`
- Check if PostgreSQL PHP extension is installed
- Ensure VPS IP is allowed in Supabase dashboard

#### 2. SSL Connection Issues:
```bash
# Test connection manually
psql "postgresql://postgres:password@db.xxx.supabase.co:5432/postgres?sslmode=require"
```

#### 3. Migration Errors:
- Check PostgreSQL vs MySQL syntax differences
- Update any MySQL-specific queries to PostgreSQL
- Use `php artisan migrate:status` to check migration state

#### 4. Performance Issues:
- Enable query logging to identify slow queries
- Add appropriate indexes in Supabase dashboard
- Consider upgrading Supabase plan for better performance

### Log Locations:
- **Laravel Logs**: `storage/logs/laravel.log`
- **Supabase Logs**: Available in Supabase dashboard
- **PostgreSQL Logs**: Via Supabase dashboard monitoring

## 📋 Supabase Deployment Checklist

- [ ] Supabase project created
- [ ] Database credentials obtained
- [ ] PostgreSQL PHP extension installed
- [ ] Laravel `.env` configured for Supabase
- [ ] Database connection tested
- [ ] Migrations run successfully
- [ ] Row Level Security configured (if needed)
- [ ] Backup strategy implemented
- [ ] Monitoring and logging configured
- [ ] Security settings optimized

Your Laravel application is now ready to run with Supabase as the database backend! 🎉

## 🆚 Supabase vs Traditional MySQL

| Feature | Supabase | MySQL/cPanel |
|---------|----------|--------------|
| Setup Complexity | Simple (cloud-hosted) | Moderate (server setup) |
| Scalability | Auto-scaling | Manual scaling |
| Backups | Automated | Manual setup required |
| Real-time Features | Built-in | Custom implementation |
| API Generation | Automatic | Manual creation |
| Cost | Free tier + usage | Server resources |
| Maintenance | Managed service | Self-managed |
| Performance | Optimized PostgreSQL | Depends on server setup |
