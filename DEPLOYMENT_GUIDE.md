# Laravel Deployment Guide for Namecheap Hosting

This guide covers deploying your Laravel application with embroidery format converter to Namecheap shared hosting or VPS.

## Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Update `.env` file for production
- [ ] Configure database credentials
- [ ] Set APP_ENV=production
- [ ] Generate new APP_KEY for production
- [ ] Configure mail settings
- [ ] Set proper file permissions

### 2. Application Optimization
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Optimize images and assets

## Deployment Options for Namecheap

### Option A: Shared Hosting (cPanel)

#### Step 1: Prepare Your Files
```bash
# In your local project directory
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 2: Upload Files via File Manager
1. Login to cPanel
2. Open File Manager
3. Navigate to `public_html` directory
4. Upload your entire Laravel project to `public_html/laravel`
5. Move contents of `public` folder to `public_html`
6. Update `index.php` in `public_html`:

```php
<?php
// Update these paths in public_html/index.php
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';
```

#### Step 3: Database Setup
1. Create MySQL database in cPanel
2. Create database user and assign privileges
3. Import your database dump
4. Update `.env` file with production credentials

#### Step 4: Configure Environment
Create `.env` file in `/laravel` directory:
```env
APP_NAME="Embroidery Management"
APP_ENV=production
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Supabase Configuration (if using)
SUPABASE_URL=your_supabase_url
SUPABASE_ANON_KEY=your_supabase_key

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Step 5: Install Libembroidery on Shared Hosting
**Note: Shared hosting may not support libembroidery installation. Consider VPS for full functionality.**

For shared hosting workarounds:
1. Contact Namecheap support to request libembroidery installation
2. Use alternative online conversion APIs
3. Upgrade to VPS hosting (Recommended - VPS Pulsar with AlmaLinux 8)

### Option B: VPS Hosting (Recommended for Libembroidery)

#### Step 1: Server Setup
```bash
# Connect via SSH
ssh root@your-server-ip

# Update system
apt update && apt upgrade -y

# Install required packages
apt install nginx mysql-server php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip unzip git composer -y
```

#### Step 2: Install Libembroidery
```bash
# Install dependencies
apt install build-essential cmake git -y

# Clone and build libembroidery
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake ..
make
make install

# Verify installation
libembroidery-convert --help
```

#### Step 3: Configure Nginx
Create `/etc/nginx/sites-available/embroidery`:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/embroidery/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # File upload size for embroidery files
    client_max_body_size 50M;
}
```

Enable the site:
```bash
ln -s /etc/nginx/sites-available/embroidery /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

#### Step 4: Deploy Application
```bash
# Clone your repository
cd /var/www
git clone https://github.com/waleedraza2221/EmbroideryFinalFinal.git embroidery
cd embroidery

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
chown -R www-data:www-data /var/www/embroidery
chmod -R 755 /var/www/embroidery
chmod -R 775 /var/www/embroidery/storage
chmod -R 775 /var/www/embroidery/bootstrap/cache

# Create embroidery temp directory
mkdir -p storage/app/temp/embroidery
chmod 775 storage/app/temp/embroidery
```

#### Step 5: Database Setup
```bash
# Setup MySQL
mysql -u root -p

CREATE DATABASE embroidery_production;
CREATE USER 'embroidery_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON embroidery_production.* TO 'embroidery_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import your database
mysql -u embroidery_user -p embroidery_production < your_database_dump.sql
```

#### Step 6: SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
apt install certbot python3-certbot-nginx -y

# Get SSL certificate
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## File Upload Configuration

### Update PHP Configuration
Edit `/etc/php/8.2/fpm/php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 512M
```

Restart PHP-FPM:
```bash
systemctl restart php8.2-fpm
```

## Testing Deployment

### 1. Basic Laravel Functionality
- [ ] Visit your domain and verify homepage loads
- [ ] Test user registration and login
- [ ] Check dashboard access
- [ ] Verify database connections

### 2. Embroidery Converter Testing
- [ ] Navigate to `/services/format-converter`
- [ ] Upload a test embroidery file
- [ ] Verify conversion works
- [ ] Test file download
- [ ] Check error handling

### 3. Performance Testing
- [ ] Test file upload limits
- [ ] Verify conversion speed
- [ ] Check memory usage during conversion
- [ ] Test concurrent conversions

## Troubleshooting Common Issues

### 1. 500 Internal Server Error
```bash
# Check Laravel logs
tail -f /var/www/embroidery/storage/logs/laravel.log

# Check Nginx error logs
tail -f /var/nginx/error.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### 2. File Permission Issues
```bash
# Fix Laravel permissions
chown -R www-data:www-data /var/www/embroidery
chmod -R 755 /var/www/embroidery
chmod -R 775 /var/www/embroidery/storage
chmod -R 775 /var/www/embroidery/bootstrap/cache
```

### 3. Libembroidery Not Found
```bash
# Check if libembroidery is installed
which libembroidery-convert

# If not found, reinstall
cd /tmp
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake ..
make && make install
```

### 4. Database Connection Issues
- Verify database credentials in `.env`
- Check if MySQL service is running: `systemctl status mysql`
- Test connection: `mysql -u username -p database_name`

## Performance Optimization

### 1. Enable OPcache
Add to `/etc/php/8.2/fpm/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. Configure Redis (Optional)
```bash
# Install Redis
apt install redis-server -y

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Queue Workers for Large Files
```bash
# Install supervisor
apt install supervisor -y

# Create queue worker configuration
cat > /etc/supervisor/conf.d/embroidery-worker.conf << EOF
[program:embroidery-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/embroidery/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/embroidery/storage/logs/worker.log
EOF

# Start supervisor
supervisorctl reread
supervisorctl update
supervisorctl start embroidery-worker:*
```

## Maintenance Tasks

### Daily Tasks
- [ ] Check application logs
- [ ] Monitor disk space
- [ ] Verify embroidery converter functionality

### Weekly Tasks
- [ ] Update system packages
- [ ] Clear old temporary files
- [ ] Check SSL certificate status
- [ ] Monitor application performance

### Monthly Tasks
- [ ] Update Laravel dependencies
- [ ] Database optimization
- [ ] Security audit
- [ ] Backup verification

## Backup Strategy

### Automated Daily Backups
Create `/home/backup_script.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/backups"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u embroidery_user -p embroidery_production > $BACKUP_DIR/db_$DATE.sql

# Backup application files
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/embroidery

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Add to crontab:
```bash
crontab -e
# Add this line for daily backup at 2 AM
0 2 * * * /home/backup_script.sh
```

## Security Checklist

- [ ] Firewall configured (UFW)
- [ ] SSH key authentication enabled
- [ ] Regular security updates
- [ ] Strong database passwords
- [ ] SSL certificate installed
- [ ] File upload restrictions
- [ ] Directory permissions secured
- [ ] Error reporting disabled in production

For any deployment issues, contact Namecheap support or refer to their Laravel hosting documentation.
