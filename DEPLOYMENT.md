# Laravel Embroidery Management System - Namecheap Pulser VPS Deployment Guide

## 🚀 Deployment Steps

### 1. Prepare Your VPS
- Access your Namecheap Pulser VPS via SSH
- Ensure you have Ubuntu 20.04/22.04 or similar

### 2. Upload Project Files
Upload your project files to the VPS using one of these methods:

#### Option A: Upload via SCP/SFTP
```bash
# From your local machine
scp -r /path/to/EmbroideryFinal root@your-vps-ip:/var/www/embroidery-app
```

#### Option B: Git Repository (Recommended)
```bash
# On your VPS
cd /var/www
sudo git clone https://github.com/your-username/your-repo.git embroidery-app
```

### 3. Run Deployment Script
```bash
# Make the script executable
chmod +x /var/www/embroidery-app/deploy.sh

# Run the deployment script
cd /var/www/embroidery-app
./deploy.sh
```

### 4. Configure Environment
```bash
# Copy production environment file
cp .env.production .env

# Update the .env file with your actual domain and settings
nano .env

# Generate application key
php artisan key:generate
```

### 5. Configure Nginx
```bash
# Copy nginx configuration
sudo cp nginx-config.conf /etc/nginx/sites-available/embroidery-app

# Update the configuration with your actual domain
sudo nano /etc/nginx/sites-available/embroidery-app

# Enable the site
sudo ln -s /etc/nginx/sites-available/embroidery-app /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test nginx configuration
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx
```

### 6. Set Up SSL Certificate (Recommended)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo systemctl enable certbot.timer
```

### 7. Configure Firewall
```bash
# Enable UFW
sudo ufw enable

# Allow SSH, HTTP, and HTTPS
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# Check status
sudo ufw status
```

### 8. Set Up Process Monitoring (Optional)
```bash
# Install Supervisor for queue workers
sudo apt install supervisor

# Create supervisor config for Laravel queues
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

### 9. Database Setup
Your project is already configured to use Supabase PostgreSQL, so no additional database setup is needed on the VPS.

### 10. Final Steps
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
sudo chown -R www-data:www-data /var/www/embroidery-app
sudo chmod -R 755 /var/www/embroidery-app
sudo chmod -R 775 /var/www/embroidery-app/storage
sudo chmod -R 775 /var/www/embroidery-app/bootstrap/cache
```

## 🔧 Important Configuration Updates

### Update 2Checkout Settings
In your production `.env` file, make sure to:
1. Set `TWOCHECKOUT_SANDBOX=false` for live payments
2. Update `APP_URL` to your actual domain
3. Configure proper return URLs in your PaymentController

### Update Payment URLs
Your PaymentController should use the production domain for return URLs:
```php
// In PaymentController::generatePaymentURL()
'return_url' => config('app.url') . '/payment/callback',
'webhook_url' => config('app.url') . '/payment/webhook',
```

## 🛡️ Security Checklist
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use HTTPS with SSL certificate
- [ ] Configure proper firewall rules
- [ ] Set strong passwords for all services
- [ ] Regular backup of database and files
- [ ] Monitor logs regularly

## 🔍 Troubleshooting

### Check Logs
```bash
# Laravel logs
tail -f /var/www/embroidery-app/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Common Issues
1. **Permission Issues**: Ensure www-data owns the files
2. **Storage Issues**: Check storage and bootstrap/cache permissions
3. **Database Connection**: Verify Supabase credentials
4. **Assets Not Loading**: Run `npm run build` and check public/build directory

## 📊 Monitoring
- Set up monitoring for server resources
- Monitor application logs for errors
- Set up uptime monitoring
- Configure email alerts for critical issues

## 🔄 Deployment Updates
For future updates:
```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache
```

## 📞 Support
Your Laravel application with 2Checkout integration is now ready for production deployment on Namecheap Pulser VPS!
