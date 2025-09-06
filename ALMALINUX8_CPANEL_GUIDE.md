# VPS Pulsar AlmaLinux 8 cPanel Deployment Guide

Complete step-by-step guide for deploying your Laravel Embroidery Management System on Namecheap VPS Pulsar with AlmaLinux 8 and cPanel.

## 🖥️ Server Information
- **OS**: AlmaLinux 8 (64-bit)
- **Control Panel**: cPanel/WHM
- **Hosting Type**: VPS Pulsar
- **Provider**: Namecheap

## 📋 Pre-Deployment Checklist

### 1. Server Access
- [ ] Received VPS IP address from Namecheap
- [ ] Received root SSH credentials
- [ ] Received WHM/cPanel login details
- [ ] Domain name pointed to VPS IP address

### 2. Local Preparation
- [ ] Laravel application ready for production
- [ ] Database exported from development
- [ ] Environment variables documented
- [ ] Domain SSL certificate (if not using Let's Encrypt)

## 🚀 Deployment Steps

### Step 1: Initial Server Setup

#### Connect to your VPS via SSH:
```bash
ssh root@your-vps-ip-address
```

#### Get and run the AlmaLinux deployment script:

**Method 1: Clone Repository (Recommended)**
```bash
git clone https://github.com/waleedraza2221/EmbroideryFinalFinal.git
cd EmbroideryFinalFinal
chmod +x almalinux8-cpanel-deploy.sh
./almalinux8-cpanel-deploy.sh
```

**Method 2: Create Script Manually (If git clone fails)**
```bash
cat > almalinux8-cpanel-deploy.sh << 'EOF'
#!/bin/bash
# Laravel Deployment Script for AlmaLinux 8 with cPanel
set -e
echo "🚀 Starting Laravel Embroidery Application Deployment..."

# Update system packages
dnf update -y
dnf install -y epel-release
dnf groupinstall -y "Development Tools"
dnf install -y cmake git wget curl

# Install PHP PostgreSQL extensions
dnf install -y ea-php82-php-gd ea-php82-php-mbstring ea-php82-php-xml ea-php82-php-bcmath ea-php82-php-zip ea-php82-php-pgsql ea-php82-php-pdo_pgsql

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install libembroidery
cd /tmp
rm -rf libembroidery
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake .. && make && make install
echo "/usr/local/lib" > /etc/ld.so.conf.d/libembroidery.conf
ldconfig

echo "✅ Server setup completed! Follow the manual steps in the guide."
EOF

chmod +x almalinux8-cpanel-deploy.sh
./almalinux8-cpanel-deploy.sh
```

This script will:
- Update AlmaLinux 8 packages
- Install development tools and dependencies
- Build and install libembroidery
- Install Composer globally
- Prepare the server for Laravel deployment

### Step 2: cPanel Account Setup

#### Access WHM (Web Host Manager):
1. Navigate to `https://your-vps-ip:2087`
2. Login with root credentials
3. Go to **Account Functions** → **Create a New Account**
4. Fill in:
   - **Domain**: yourdomain.com
   - **Username**: choose a username (e.g., "embuser")
   - **Password**: strong password
   - **Package**: Select appropriate hosting package

#### Access cPanel:
1. Navigate to `https://your-vps-ip:2083`
2. Login with the account credentials created above

### Step 3: Supabase Database Setup

Instead of using cPanel MySQL, we'll use Supabase for better scalability and features:

#### Create Supabase Project:
1. **Visit**: https://supabase.com/dashboard
2. **Sign up/Login** with GitHub or email
3. **Create New Project**:
   - Project name: `embroidery-management`
   - Database password: Generate strong password
   - Region: Choose closest to your VPS location

#### Get Supabase Credentials:
1. **Database Settings** (Settings → Database):
   - Host: `db.xxx.supabase.co`
   - Database: `postgres`
   - Port: `5432`
   - User: `postgres`
   - Password: Your project password

2. **API Settings** (Settings → API):
   - Project URL: `https://xxx.supabase.co`
   - Anon key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
   - Service role key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`

### Step 4: PHP PostgreSQL Configuration

#### In cPanel, go to **Software** → **MultiPHP INI Editor**:

Select your domain and update these settings:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 512M
max_input_vars = 3000
```

#### Verify PostgreSQL PHP Extension:
The deployment script should have installed PostgreSQL support. Verify by:
- Go to **Software** → **PHP Version** 
- Check that `pgsql` and `pdo_pgsql` extensions are enabled

#### Set PHP Version:
- Go to **MultiPHP Manager**
- Select PHP 8.2 for your domain

### Step 5: Application Deployment

#### Method A: Using cPanel File Manager (Recommended)

1. **Prepare your application locally**:
```bash
# In your local project directory
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan cache:clear
```

2. **Create deployment ZIP**:
   - Zip your entire Laravel project
   - Exclude: `.git`, `node_modules`, `.env`

3. **Upload via cPanel File Manager**:
   - Go to **Files** → **File Manager**
   - Navigate to `public_html`
   - Upload your ZIP file
   - Extract the ZIP file
   - Move Laravel files to appropriate location

#### Method B: Using Git (Alternative)

```bash
# SSH into your server
ssh username@your-vps-ip

# Navigate to public_html
cd public_html

# Clone your repository (public repository, no authentication needed)
git clone https://github.com/waleedraza2221/EmbroideryFinalFinal.git .

# Install dependencies
composer install --optimize-autoloader --no-dev
```

**Note**: If git clone fails due to authentication, use Method A (cPanel File Manager) instead.

### Step 6: Laravel Configuration

#### Create `.env` file in your application root:
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

# Mail Configuration (using cPanel email)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Libembroidery Configuration
LIBEMBROIDERY_PATH=/usr/local/bin/libembroidery-convert
CONVERSION_TIMEOUT=300

# Session and Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
```

#### Run Laravel optimization commands:
```bash
# SSH into your account
ssh username@your-vps-ip
cd public_html

# Generate application key
php artisan key:generate

# Test Supabase connection
php artisan tinker
# In tinker console: DB::connection()->getPdo();
# Should return PDO object without errors

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

### Step 7: Web Server Configuration

#### For Subdirectory Installation:
If you installed Laravel in a subdirectory (e.g., `/public_html/laravel/`):

1. Create `.htaccess` in `public_html`:
```apache
RewriteEngine On
RewriteRule ^(.*)$ laravel/public/$1 [L]
```

#### For Root Installation:
If you moved Laravel's `public` folder contents to `public_html`:

1. Update `index.php` in `public_html`:
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
```

### Step 8: SSL Certificate Setup

#### Using Let's Encrypt (Free):
1. In cPanel, go to **Security** → **SSL/TLS**
2. Click **Let's Encrypt SSL**
3. Select your domain
4. Click **Issue**

#### Using Custom Certificate:
1. Go to **SSL/TLS** → **Manage SSL Sites**
2. Upload your certificate files
3. Click **Install Certificate**

### Step 9: Testing and Verification

#### Test Basic Functionality:
- [ ] Visit `https://yourdomain.com` - Homepage loads
- [ ] Test user registration and login
- [ ] Access dashboard functionality
- [ ] Check database connectivity

#### Test Embroidery Converter:
- [ ] Navigate to `/services/format-converter`
- [ ] Upload a test embroidery file (.dst, .pes, etc.)
- [ ] Verify conversion works properly
- [ ] Test file download functionality
- [ ] Check error handling

#### Performance Testing:
- [ ] Upload large embroidery files (up to 50MB)
- [ ] Test multiple simultaneous conversions
- [ ] Monitor server resource usage

## 🔧 Troubleshooting

### Common Issues and Solutions:

#### 1. "Libembroidery not found" Error
```bash
# SSH into server and check installation
which libembroidery-convert
/usr/local/bin/libembroidery-convert --help

# If not found, reinstall:
cd /tmp && rm -rf libembroidery
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery && mkdir build && cd build
cmake .. && make && make install
ldconfig
```

#### 2. File Permission Issues
```bash
# Fix permissions via SSH
cd /home/username/public_html
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

#### 3. Large File Upload Issues
- Check PHP configuration in MultiPHP INI Editor
- Verify `upload_max_filesize` and `post_max_size`
- Check disk space in cPanel

#### 4. Database Connection Issues
- Verify database credentials in `.env`
- Check database user permissions in cPanel
- Test connection via cPanel phpMyAdmin

### Log Files Location:
- **Laravel Logs**: `/home/username/public_html/storage/logs/laravel.log`
- **cPanel Error Logs**: Access via cPanel → **Metrics** → **Errors**
- **PHP Error Logs**: Check via cPanel → **Metrics** → **Raw Access**

## 📈 Performance Optimization

### 1. Enable OPcache
In MultiPHP INI Editor, add:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
```

### 2. Configure Caching
Update `.env` for better performance:
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 3. Database Optimization
- Regular database cleanup via cPanel
- Enable query caching in MySQL
- Optimize tables monthly

## 🔒 Security Best Practices

### Server Security:
- [ ] Change default SSH port
- [ ] Configure firewall rules
- [ ] Regular security updates
- [ ] Strong passwords everywhere
- [ ] Disable root SSH login

### Application Security:
- [ ] Keep Laravel updated
- [ ] Regular dependency updates
- [ ] Enable HTTPS everywhere
- [ ] Configure proper file permissions
- [ ] Regular security audits

## 🔄 Backup Strategy

### Automated Backups via cPanel:
1. Go to **Files** → **Backup Wizard**
2. Set up full account backups
3. Schedule daily/weekly backups
4. Store backups off-site

### Manual Database Backups:
```bash
# Via SSH
mysqldump -u username_emb_user -p username_embroidery_prod > backup_$(date +%Y%m%d).sql
```

## 📞 Support and Maintenance

### Regular Maintenance Tasks:
- **Daily**: Check error logs and application functionality
- **Weekly**: Update system packages and clear temporary files  
- **Monthly**: Database optimization and security updates
- **Quarterly**: Full security audit and backup verification

### Getting Help:
- **Namecheap Support**: For VPS and cPanel issues
- **Laravel Documentation**: For application-specific problems
- **AlmaLinux Documentation**: For OS-related issues

## ✅ Deployment Checklist

- [ ] Server setup completed
- [ ] cPanel account configured
- [ ] Database created and configured
- [ ] PHP settings optimized
- [ ] Application uploaded and configured
- [ ] Laravel optimization completed
- [ ] SSL certificate installed
- [ ] Embroidery converter tested
- [ ] Backups configured
- [ ] Security measures implemented
- [ ] Performance testing completed

Your Laravel Embroidery Management System is now ready for production use on AlmaLinux 8 with cPanel! 🎉
