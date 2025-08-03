#!/bin/bash

# Laravel Application Health Check Script
APP_URL="https://your-domain.com"
LOG_FILE="/var/log/embroidery-health.log"

echo "🏥 Starting health check - $(date)" | tee -a $LOG_FILE

# Check if the application is responding
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" $APP_URL)

if [ $HTTP_STATUS -eq 200 ]; then
    echo "✅ Application is healthy - HTTP $HTTP_STATUS" | tee -a $LOG_FILE
else
    echo "❌ Application is unhealthy - HTTP $HTTP_STATUS" | tee -a $LOG_FILE
    
    # Optional: Send alert email
    # echo "Application health check failed - HTTP $HTTP_STATUS" | mail -s "Alert: Application Down" admin@your-domain.com
    
    # Optional: Restart services
    # sudo systemctl restart nginx
    # sudo systemctl restart php8.2-fpm
fi

# Check disk usage
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "⚠️  Warning: Disk usage is at ${DISK_USAGE}%" | tee -a $LOG_FILE
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.0f", ($3/$2) * 100.0)}')
if [ $MEMORY_USAGE -gt 80 ]; then
    echo "⚠️  Warning: Memory usage is at ${MEMORY_USAGE}%" | tee -a $LOG_FILE
fi

# Check if PHP-FPM is running
if ! pgrep -f php-fpm > /dev/null; then
    echo "❌ PHP-FPM is not running" | tee -a $LOG_FILE
    sudo systemctl restart php8.2-fpm
fi

# Check if Nginx is running
if ! pgrep -f nginx > /dev/null; then
    echo "❌ Nginx is not running" | tee -a $LOG_FILE
    sudo systemctl restart nginx
fi

echo "🏥 Health check completed - $(date)" | tee -a $LOG_FILE
echo "---" | tee -a $LOG_FILE
