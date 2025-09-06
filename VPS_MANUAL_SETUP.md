# Quick VPS Setup Commands for AlmaLinux 8

Since GitHub authentication is causing issues, here are the direct commands to run on your VPS:

## 🚀 **Copy and Paste This Script on Your VPS**

SSH into your VPS as root and run these commands one by one:

```bash
# Update system packages
echo "Updating system packages..."
dnf update -y

# Install EPEL repository
echo "Installing EPEL repository..."
dnf install -y epel-release

# Install development tools
echo "Installing development tools..."
dnf groupinstall -y "Development Tools"
dnf install -y cmake git wget curl

# Install PHP PostgreSQL extensions for Supabase
echo "Installing PHP PostgreSQL extensions..."
dnf install -y ea-php82-php-gd ea-php82-php-mbstring ea-php82-php-xml ea-php82-php-bcmath ea-php82-php-zip ea-php82-php-pgsql ea-php82-php-pdo_pgsql

# Install Composer globally
echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install libembroidery
echo "Installing libembroidery..."
cd /tmp
rm -rf libembroidery
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
echo "Verifying libembroidery installation..."
if command -v libembroidery-convert &> /dev/null; then
    echo "✅ Libembroidery installed successfully!"
    libembroidery-convert --help | head -5
else
    echo "❌ Libembroidery installation failed"
fi

echo ""
echo "🎉 Server setup completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Set up Supabase database at https://supabase.com/dashboard"
echo "2. Create cPanel account for your domain"
echo "3. Upload Laravel application via cPanel File Manager"
echo "4. Configure .env file with Supabase credentials"
echo "5. Run Laravel optimization commands"
```

## 🔧 **Alternative: One-Command Installation**

Or run this single command to create and execute the script:

```bash
curl -s https://pastebin.com/raw/SCRIPT_ID | bash
```

**Note**: Since GitHub raw files aren't accessible, I'll provide the manual commands above.

## 📁 **Manual Laravel Deployment Steps**

Since git clone is having authentication issues, use these alternative methods:

### **Method 1: Download ZIP from GitHub**
1. Go to https://github.com/waleedraza2221/EmbroideryFinalFinal
2. Click "Code" → "Download ZIP"
3. Upload ZIP file via cPanel File Manager
4. Extract in public_html directory

### **Method 2: Use wget for Public Files**
```bash
# If the repository is public, try:
wget https://github.com/waleedraza2221/EmbroideryFinalFinal/archive/main.zip
unzip main.zip
mv EmbroideryFinalFinal-main/* ./
```

### **Method 3: Manual File Transfer**
1. Use SFTP/SCP to upload your local Laravel files
2. Or use cPanel File Manager to upload ZIP file
3. Extract and configure as needed

## 🎯 **Complete VPS Setup Commands**

Here's the complete script you can run on your VPS:

```bash
#!/bin/bash
echo "🚀 Setting up AlmaLinux 8 VPS for Laravel Embroidery Management..."

# System updates and packages
dnf update -y
dnf install -y epel-release
dnf groupinstall -y "Development Tools"
dnf install -y cmake git wget curl unzip

# PHP extensions for Laravel and Supabase
dnf install -y ea-php82-php-gd ea-php82-php-mbstring ea-php82-php-xml \
    ea-php82-php-bcmath ea-php82-php-zip ea-php82-php-pgsql ea-php82-php-pdo_pgsql

# Composer installation
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Libembroidery installation
cd /tmp
rm -rf libembroidery
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake .. && make && make install
echo "/usr/local/lib" > /etc/ld.so.conf.d/libembroidery.conf
ldconfig

# Verification
echo ""
echo "🔍 Verifying installations..."
echo "Composer version: $(composer --version)"
echo "Libembroidery: $(libembroidery-convert --help | head -1)"
echo ""
echo "✅ VPS setup completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Create Supabase project"
echo "2. Configure cPanel account"
echo "3. Upload Laravel application"
echo "4. Configure environment variables"
echo "5. Run migrations and optimization"
```

Run these commands step by step on your VPS, and then proceed with the manual Laravel deployment via cPanel File Manager upload! 🚀
