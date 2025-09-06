# Shared Hosting Deployment Guide

## Overview
Deploy your complete Laravel embroidery management system on Namecheap shared hosting using Git.

## Prerequisites
- Namecheap shared hosting account
- Git access on shared hosting
- PHP 8.1+ support
- Composer available

## Step 1: Prepare Your Repository

Your code is already ready in GitHub: `waleedraza2221/EmbroideryFinalFinal`

## Step 2: Connect to Shared Hosting

1. **Access cPanel or SSH** (if available)
2. **Navigate to your domain's document root** (usually `public_html`)

## Step 3: Deploy via Git

```bash
# Clone your repository
git clone https://github.com/waleedraza2221/EmbroideryFinalFinal.git .

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 4: Configure Environment (.env)

```env
APP_NAME="Embroidery Management"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=db.your-supabase-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

# 2Checkout Live Settings
TWOCHECKOUT_ACCOUNT_NUMBER=your-account-number
TWOCHECKOUT_SECRET_KEY=your-secret-key
TWOCHECKOUT_PUBLISHABLE_KEY=your-publishable-key
TWOCHECKOUT_ENVIRONMENT=production

# Email settings
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Remove VPS API settings (not needed)
# EMBROIDERY_API_URL=
# EMBROIDERY_API_KEY=
```

## Step 5: Install Python for Embroidery Conversion

On most shared hosting, you can install Python packages in your user directory:

```bash
# Check if Python 3 is available
python3 --version

# Install PyEmbroidery in user directory
pip3 install --user pyembroidery

# Or if pip3 is not available
python3 -m pip install --user pyembroidery
```

## Step 6: Update Embroidery Controller for Local Processing

The system will now process embroidery files locally instead of using the VPS API.

## Step 7: File Permissions

```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app
```

## Step 8: Configure Document Root

In cPanel, make sure your domain points to the `public` folder of your Laravel installation.

## Step 9: Test the Installation

1. Visit your domain
2. Test user registration/login
3. Test embroidery file upload and conversion
4. Test payment processing with 2Checkout
5. Test email notifications

## Deployment Commands (for updates)

```bash
# Pull latest changes
git pull origin main

# Update dependencies if needed
composer install --optimize-autoloader --no-dev

# Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run any new migrations
php artisan migrate --force
```

## Benefits of Shared Hosting Deployment

✅ **Simpler Setup** - Everything in one place
✅ **Cost Effective** - No separate VPS costs
✅ **Easier Maintenance** - Single server to manage
✅ **Built-in Backups** - Hosting provider handles backups
✅ **SSL Included** - Most shared hosts include free SSL
✅ **Email Integration** - Easy email setup with hosting

## File Structure on Shared Hosting

```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/ (document root points here)
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
└── README.md
```

This approach eliminates the complexity of managing a separate VPS while still providing all the functionality you need!
