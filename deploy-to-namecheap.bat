@echo off
echo 🚀 Namecheap Git Deployment Helper
echo.

REM Colors don't work well in batch, so using simple text
echo [INFO] This script will help you deploy to Namecheap shared hosting
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo [ERROR] Laravel artisan file not found. Make sure you're in the Laravel root directory.
    pause
    exit /b 1
)

echo [STEP 1] Setting up local Git repository...
git add .
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to add files to Git
    pause
    exit /b 1
)

git commit -m "Deploy Laravel Embroidery Management System to Namecheap"
if %ERRORLEVEL% neq 0 (
    echo [WARNING] Commit failed - files may already be committed
)

echo.
echo [INFO] Now you need to manually set up the remote connection.
echo.
echo Please run this command with your actual Namecheap details:
echo.
echo git remote add origin ssh://YOUR-CPANEL-USERNAME@YOUR-SERVER.web-hosting.com:21098/home/YOUR-CPANEL-USERNAME/public_html
echo.
echo Replace:
echo   YOUR-CPANEL-USERNAME = Your cPanel username
echo   YOUR-SERVER = Your hosting server (e.g., server123.web-hosting.com)
echo.

set /p "continue=Have you set up the remote origin? (y/n): "
if /i "%continue%" neq "y" (
    echo.
    echo [INFO] Please set up the remote origin first, then run this script again.
    pause
    exit /b 0
)

echo.
echo [STEP 2] Pushing to Namecheap server...
echo [INFO] Git will ask for your cPanel password
echo.

git push origin main
if %ERRORLEVEL% neq 0 (
    echo.
    echo [ERROR] Push failed. Please check:
    echo   1. Remote origin is set correctly
    echo   2. SSH access is enabled in cPanel
    echo   3. Server details are correct
    echo   4. You entered the correct cPanel password
    pause
    exit /b 1
)

echo.
echo [SUCCESS] Files pushed to server successfully!
echo.
echo [NEXT STEPS]
echo 1. SSH into your server: ssh YOUR-CPANEL-USERNAME@YOUR-SERVER.web-hosting.com -p 21098
echo 2. Navigate to your domain folder: cd /public_html
echo 3. Run the setup script: ./deploy-shared-hosting.sh
echo 4. Configure your .env file with real credentials
echo 5. Set your domain's document root to the 'public' folder
echo.
echo [TEST URLS]
echo   Main site: https://yourdomain.com
echo   Health check: https://yourdomain.com/health-check.php
echo.

pause
