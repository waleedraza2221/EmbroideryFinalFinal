# 🚀 Quick Shared Hosting Deployment

## Your Setup Decision: ✅ Namecheap Shared Hosting Only

Since you have Git access on your shared hosting, we're deploying everything in one place. This is much simpler and more cost-effective!

## Step 1: Upload to GitHub (Already Done ✅)
Your code is ready at: `https://github.com/waleedraza2221/EmbroideryFinalFinal`

## Step 2: Deploy on Namecheap Shared Hosting

### 🎯 **Recommended: Using Namecheap's Git Integration**

Based on [Namecheap's official Git guide](https://www.namecheap.com/support/knowledgebase/article.aspx/9586/89/how-to-deal-with-git-on-our-shared-servers/):

#### Option A: Automated Deployment (Recommended)
```batch
# Run the automated deployment helper (Windows)
deploy-to-namecheap.bat
```

#### Option B: Manual Git Deployment
```bash
# 1. Set up local repository
git add .
git commit -m "Deploy Laravel Embroidery Management System"

# 2. Add Namecheap remote (replace with your details)
git remote add origin ssh://your-cpanel-username@your-server.web-hosting.com:21098/home/your-cpanel-username/public_html

# 3. SSH to server and prepare
ssh your-cpanel-username@your-server.web-hosting.com -p 21098
cd /public_html
git init
git config receive.denyCurrentBranch updateInstead

# 4. Push from local (will ask for cPanel password)
git push origin main

# 5. Run setup on server
./deploy-shared-hosting.sh
```

**📋 See complete instructions: [NAMECHEAP_GIT_DEPLOY.md](./NAMECHEAP_GIT_DEPLOY.md)**

### Option C: Manual Upload via cPanel File Manager
1. Download your repository as ZIP from GitHub
2. Extract to your computer
3. Upload all files to `public_html` via cPanel File Manager
4. Set folder permissions: `storage` and `bootstrap/cache` to 755

## Step 3: Configure Environment
```bash
# Copy the shared hosting environment template
cp .env.shared-hosting .env

# Edit .env with your actual details:
nano .env  # or use cPanel File Manager editor
```

**Update these in your .env:**
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

## Step 4: Final Configuration
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 5: Install Python Package (if needed)
```bash
# Most shared hosting has Python 3
python3 -m pip install --user pyembroidery
```

## Step 6: Set Document Root
In cPanel, set your domain's document root to point to the `public` folder of your Laravel installation.

## Step 7: Test Everything
1. **Visit your site**: `https://yourdomain.com`
2. **Health check**: `https://yourdomain.com/health-check.php`
3. **Register a user** and test login
4. **Upload an embroidery file** and test conversion
5. **Test payment** with 2Checkout

## What You Get With This Setup:

✅ **Complete Laravel Application**
- User registration/login
- Admin dashboard with sidebar
- Customer management
- Order processing with invoices

✅ **Embroidery Format Converter**
- Supports 12 formats (DST, PES, JEF, EXP, VP3, XXX, PCS, HUS, SEW, PEC, VIP, CSD)
- Uses PyEmbroidery (Python-based, shared hosting compatible)
- Local file processing (no external API needed)

✅ **Payment Integration**
- 2Checkout live mode configured
- Automatic invoice generation
- Order tracking

✅ **Production Ready**
- Optimized for shared hosting
- Error logging
- Security headers
- File cleanup automation

## Benefits of Shared Hosting Deployment:

🎯 **Simplified Management** - Everything in one place
💰 **Cost Effective** - No VPS costs
🔧 **Easy Maintenance** - Single environment
📧 **Built-in Email** - Use your domain's email
🔒 **SSL Included** - Most hosts provide free SSL
📱 **cPanel Access** - User-friendly management interface

## File Structure on Your Hosting:
```
public_html/
├── app/
├── config/
├── database/
├── public/ ← Your domain points here
├── resources/
├── routes/
├── scripts/
│   └── convert_embroidery.py ← Embroidery converter
├── storage/
├── .env ← Your configuration
└── artisan
```

## Support & Troubleshooting:
- **Health Check**: Visit `/health-check.php` to verify all components
- **Logs**: Check `storage/logs/laravel.log` for any errors
- **Permissions**: Ensure `storage` folder is writable

Your embroidery management system is now production-ready on shared hosting! 🎉
