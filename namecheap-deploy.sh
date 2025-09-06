#!/bin/bash

# Laravel Deployment Script for Namecheap VPS
# Run this script on your VPS after initial server setup

set -e

echo "🚀 Starting Laravel Embroidery Application Deployment..."

# Configuration
DOMAIN="yourdomain.com"
APP_DIR="/var/www/embroidery"
REPO_URL="https://github.com/waleedraza2221/EmbroideryFinalFinal.git"
DB_NAME="embroidery_production"
DB_USER="embroidery_user"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root"
    exit 1
fi

# Update system
print_status "Updating system packages..."
apt update && apt upgrade -y

# Install required packages
print_status "Installing required packages..."
apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
    unzip git composer build-essential cmake

# Install libembroidery
print_status "Installing libembroidery..."
cd /tmp
if [ ! -d "libembroidery" ]; then
    git clone https://github.com/Embroidermodder/libembroidery.git
fi
cd libembroidery
mkdir -p build && cd build
cmake ..
make
make install
ldconfig

# Verify libembroidery installation
if command -v libembroidery-convert &> /dev/null; then
    print_status "Libembroidery installed successfully"
else
    print_error "Libembroidery installation failed"
    exit 1
fi

# Create application directory and clone repository
print_status "Cloning application repository..."
if [ -d "$APP_DIR" ]; then
    rm -rf $APP_DIR
fi
git clone $REPO_URL $APP_DIR
cd $APP_DIR

# Install Composer dependencies
print_status "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Set up environment file
print_status "Setting up environment configuration..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Set permissions
print_status "Setting file permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Create embroidery temp directory
mkdir -p $APP_DIR/storage/app/temp/embroidery
chmod 775 $APP_DIR/storage/app/temp/embroidery

# Configure PHP
print_status "Configuring PHP settings..."
PHP_INI="/etc/php/8.2/fpm/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' $PHP_INI
sed -i 's/post_max_size = .*/post_max_size = 50M/' $PHP_INI
sed -i 's/max_execution_time = .*/max_execution_time = 300/' $PHP_INI
sed -i 's/memory_limit = .*/memory_limit = 512M/' $PHP_INI

# Restart PHP-FPM
systemctl restart php8.2-fpm

# Configure Nginx
print_status "Configuring Nginx..."
cat > /etc/nginx/sites-available/embroidery << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    client_max_body_size 50M;
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/embroidery /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Database setup prompt
print_warning "Database setup required!"
echo "Please run the following commands to set up your database:"
echo ""
echo "mysql -u root -p"
echo "CREATE DATABASE $DB_NAME;"
echo "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY 'your_strong_password';"
echo "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
echo "FLUSH PRIVILEGES;"
echo "EXIT;"
echo ""
echo "Then update your .env file with the database credentials."

# Laravel optimization
print_status "Optimizing Laravel application..."
cd $APP_DIR
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Install SSL (Let's Encrypt)
print_warning "SSL Certificate Setup"
echo "To install SSL certificate, run:"
echo "apt install certbot python3-certbot-nginx -y"
echo "certbot --nginx -d $DOMAIN -d www.$DOMAIN"

# Final instructions
print_status "Deployment completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Update .env file with your production settings"
echo "2. Set up database using the commands shown above"
echo "3. Import your database dump"
echo "4. Install SSL certificate"
echo "5. Test the application at http://$DOMAIN"
echo ""
echo "📂 Application Directory: $APP_DIR"
echo "📋 Nginx Config: /etc/nginx/sites-available/embroidery"
echo "📋 PHP Config: /etc/php/8.2/fpm/php.ini"
echo ""
echo "🔧 Useful Commands:"
echo "- Check Laravel logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "- Check Nginx logs: tail -f /var/log/nginx/error.log"
echo "- Restart services: systemctl restart nginx php8.2-fpm"
echo ""
echo "✅ Your Laravel application with embroidery converter is ready!"
