#!/bin/bash

# Laravel Deployment Script for Namecheap Pulser VPS
echo "🚀 Starting Laravel deployment on Namecheap Pulser VPS..."

# Update system packages
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
echo "🔧 Installing required packages..."
sudo apt install -y nginx mysql-server php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-dom php8.2-pgsql composer unzip git curl

# Install Node.js and npm for Vite
echo "📱 Installing Node.js and npm..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Create application directory
echo "📁 Creating application directory..."
sudo mkdir -p /var/www/embroidery-app
sudo chown -R $USER:$USER /var/www/embroidery-app

# Clone or copy project files (you'll need to upload your files first)
echo "📂 Setting up project directory..."
cd /var/www/embroidery-app

# Install Composer dependencies
echo "📚 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Install npm dependencies and build assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# Set proper permissions
echo "🔒 Setting proper permissions..."
sudo chown -R www-data:www-data /var/www/embroidery-app
sudo chmod -R 755 /var/www/embroidery-app
sudo chmod -R 775 /var/www/embroidery-app/storage
sudo chmod -R 775 /var/www/embroidery-app/bootstrap/cache

# Generate application key if not exists
echo "🔑 Generating application key..."
php artisan key:generate

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment script completed!"
echo "🌐 Next: Configure Nginx and update your .env file"
