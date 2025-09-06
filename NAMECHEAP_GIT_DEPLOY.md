# 🚀 Namecheap Git Deployment Guide

## Based on Official Namecheap Documentation

Using the exact process from: https://www.namecheap.com/support/knowledgebase/article.aspx/9586/89/how-to-deal-with-git-on-our-shared-servers/

## Prerequisites ✅
- Namecheap shared hosting account
- SSH access enabled (contact support if needed)
- Your cPanel username and server details

## Step 1: Set Up Local Repository (Your Computer)

```bash
cd C:\Users\ABC\Desktop\EmbroideryFinal

# Configure Git (if not already done)
git config --global user.name "Your Name"
git config --global user.email "youremail@example.com"

# Add all files to Git
git add .
git commit -m "Initial commit - Laravel Embroidery Management System"
```

## Step 2: Create Remote Repository on Namecheap Server

**SSH into your Namecheap hosting:**
```bash
ssh your-cpanel-username@your-server.web-hosting.com -p 21098
```

**Navigate to your domain folder:**
```bash
# For main domain
cd /public_html

# OR for addon domain
cd /public_html/yourdomain.com
```

**Initialize Git on server:**
```bash
git init
git config receive.denyCurrentBranch updateInstead
```

## Step 3: Connect Local to Remote

**On your local computer:**
```bash
cd C:\Users\ABC\Desktop\EmbroideryFinal

# Add remote origin (replace with your actual details)
git remote add origin ssh://your-cpanel-username@your-server.web-hosting.com:21098/home/your-cpanel-username/public_html
```

**Example with real values:**
```bash
git remote add origin ssh://myusername@server123.web-hosting.com:21098/home/myusername/public_html
```

## Step 4: Deploy Your Laravel App

**Push to server:**
```bash
git push origin main
```
*(Git will ask for your cPanel password)*

## Step 5: Complete Server Setup

**SSH back into your server and run:**
```bash
cd /public_html

# Make deployment script executable
chmod +x deploy-shared-hosting.sh

# Run the automated setup
./deploy-shared-hosting.sh
```

## Step 6: Configure Environment

**Edit your .env file on the server:**
```bash
# Copy the shared hosting template
cp .env.shared-hosting .env

# Edit with real values
nano .env
```

**Update these critical settings:**
```env
APP_URL=https://yourdomain.com
DB_HOST=db.your-supabase-project.supabase.co
DB_PASSWORD=your-supabase-password
MAIL_HOST=mail.yourdomain.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-email-password
TWOCHECKOUT_ACCOUNT_NUMBER=your-account-number
TWOCHECKOUT_SECRET_KEY=your-secret-key
TWOCHECKOUT_PUBLISHABLE_KEY=your-publishable-key
```

**Generate application key and set up database:**
```bash
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 7: Set Document Root

In cPanel:
1. Go to **Subdomains** or **Addon Domains**
2. Set the document root to point to the `public` folder
3. Example: `/public_html/public` (if installed in main directory)

## Step 8: Install Python Dependencies

```bash
# Install PyEmbroidery for embroidery conversion
python3 -m pip install --user pyembroidery

# Test the installation
python3 -c "import pyembroidery; print('PyEmbroidery installed successfully')"
```

## Step 9: Test Your Deployment

1. **Visit your website:** `https://yourdomain.com`
2. **Health check:** `https://yourdomain.com/health-check.php`
3. **Test registration and login**
4. **Test embroidery file upload and conversion**
5. **Test payment processing**

## Future Updates (After Initial Deployment)

**When you make changes locally:**
```bash
# On your computer
git add .
git commit -m "Description of changes"
git push origin main
```

**On the server (if needed):**
```bash
# Clear caches after updates
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### SSH Connection Issues
- Ensure SSH is enabled in cPanel
- Use the correct port (21098 for Namecheap)
- Verify your server name from cPanel

### Git Push Issues
```bash
# If you get permission errors
git config receive.denyCurrentBranch updateInstead
```

### Python/PyEmbroidery Issues
```bash
# Check Python availability
python3 --version

# Install in user directory
python3 -m pip install --user pyembroidery

# Check installation
python3 scripts/convert_embroidery.py --help
```

### File Permissions
```bash
# If you get permission errors
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app
```

## Key Differences from Standard Git

1. **Non-bare repository** - Namecheap uses non-bare repos
2. **Special config needed** - `receive.denyCurrentBranch updateInstead`
3. **SSH port** - Uses port 21098 instead of standard 22
4. **Automatic deployment** - Files appear immediately after push

## Your Server Details Format

Replace these with your actual Namecheap details:
- **Username**: Your cPanel username
- **Server**: Your hosting server (e.g., server123.web-hosting.com)
- **Port**: 21098 (Namecheap standard)
- **Path**: /home/your-username/public_html

## Success Indicators ✅

- [ ] Git push completes without errors
- [ ] Website loads at your domain
- [ ] Health check shows all green
- [ ] User registration works
- [ ] Embroidery upload/conversion works
- [ ] Payment processing works
- [ ] Admin dashboard accessible

Your Laravel Embroidery Management System is now live on Namecheap shared hosting! 🎉
