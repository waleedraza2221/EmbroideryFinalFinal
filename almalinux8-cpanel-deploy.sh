#!/bin/bash

# Laravel Deployment Script for Namecheap VPS Pulsar
# AlmaLinux 8 with cPanel
# Run this script as root user

set -e

echo "🚀 Starting Laravel Embroidery Application Deployment on AlmaLinux 8..."

# Configuration
DOMAIN="yourdomain.com"
APP_DIR="/home/username/public_html"  # Update with your cPanel username
REPO_URL="https://github.com/waleedraza2221/EmbroideryFinalFinal.git"
DB_NAME="username_embroidery"  # Update with your cPanel username prefix
DB_USER="username_emb"         # Update with your cPanel username prefix

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root"
    exit 1
fi

print_info "Deploying on AlmaLinux 8 with cPanel..."

# Update system packages
print_status "Updating system packages..."
dnf update -y

# Install EPEL repository
print_status "Installing EPEL repository..."
dnf install -y epel-release

# Install development tools
print_status "Installing development tools..."
dnf groupinstall -y "Development Tools"
dnf install -y cmake git wget curl

# Install additional PHP modules that might not be included with cPanel
print_status "Installing additional PHP modules..."
dnf install -y ea-php82-php-gd ea-php82-php-mbstring ea-php82-php-xml ea-php82-php-bcmath ea-php82-php-zip ea-php82-php-pgsql ea-php82-php-pdo_pgsql

# Install Composer globally
print_status "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install libembroidery
print_status "Installing libembroidery..."
cd /tmp
if [ -d "libembroidery" ]; then
    rm -rf libembroidery
fi
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake ..
make
make install

# Update library path
echo "/usr/local/lib" > /etc/ld.so.conf.d/libembroidery.conf
ldconfig

# Verify libembroidery installation
if command -v libembroidery-convert &> /dev/null; then
    print_status "Libembroidery installed successfully"
    libembroidery-convert --help | head -5
else
    print_error "Libembroidery installation failed"
    exit 1
fi

print_warning "=== MANUAL STEPS REQUIRED ==="
echo ""
print_info "1. Supabase Database Setup:"
echo "   - Create Supabase project at https://supabase.com/dashboard"
echo "   - Get database credentials from Settings → Database"
echo "   - Get API keys from Settings → API"
echo "   - Configure .env with Supabase PostgreSQL credentials"
echo ""

print_info "2. Application Deployment via cPanel:"
echo "   - Login to cPanel File Manager"
echo "   - Navigate to public_html directory"
echo "   - Upload your Laravel project as ZIP file"
echo "   - Extract the ZIP file"
echo "   - Move Laravel files to public_html root OR create subdirectory"
echo ""

print_info "3. PHP Configuration via cPanel:"
echo "   - Go to MultiPHP INI Editor"
echo "   - Select your domain"
echo "   - Update these settings:"
echo "     upload_max_filesize = 50M"
echo "     post_max_size = 50M"
echo "     max_execution_time = 300"
echo "     memory_limit = 512M"
echo ""

print_info "4. SSL Certificate:"
echo "   - Go to SSL/TLS in cPanel"
echo "   - Use Let's Encrypt (free) or upload your certificate"
echo ""

print_status "Server preparation completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Complete the manual cPanel configuration above"
echo "2. Upload your Laravel application"
echo "3. Configure .env file with database credentials"
echo "4. Run Laravel optimization commands via SSH or Terminal in cPanel"
echo ""
echo "🔧 Laravel Commands to run after upload:"
echo "cd /home/username/public_html"
echo "composer install --optimize-autoloader --no-dev"
echo "php artisan key:generate"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo "php artisan view:cache"
echo "php artisan migrate"
echo ""
echo "✅ AlmaLinux 8 server ready for Laravel deployment!"
