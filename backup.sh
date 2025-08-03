#!/bin/bash

# Laravel Application Backup Script
BACKUP_DIR="/home/backups"
APP_DIR="/var/www/embroidery-app"
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="embroidery_backup_$DATE"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

echo "🔄 Starting backup process..."

# Create application backup (excluding node_modules and vendor)
echo "📁 Backing up application files..."
tar -czf "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" \
    --exclude="$APP_DIR/node_modules" \
    --exclude="$APP_DIR/vendor" \
    --exclude="$APP_DIR/storage/logs/*.log" \
    --exclude="$APP_DIR/.git" \
    -C "$APP_DIR" .

# Backup .env file separately for security
echo "🔒 Backing up environment configuration..."
cp "$APP_DIR/.env" "$BACKUP_DIR/${BACKUP_NAME}_env.backup"

# Note: Database backup not needed as we're using Supabase (cloud-hosted)
echo "ℹ️  Database backup skipped - using Supabase cloud database"

# Clean up old backups (keep only last 7 days)
echo "🧹 Cleaning up old backups..."
find $BACKUP_DIR -name "embroidery_backup_*" -type f -mtime +7 -delete

echo "✅ Backup completed successfully!"
echo "📦 Files: $BACKUP_DIR/${BACKUP_NAME}_files.tar.gz"
echo "⚙️  Config: $BACKUP_DIR/${BACKUP_NAME}_env.backup"

# Optional: Upload to cloud storage (uncomment and configure as needed)
# echo "☁️  Uploading to cloud storage..."
# aws s3 cp "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" s3://your-backup-bucket/
# aws s3 cp "$BACKUP_DIR/${BACKUP_NAME}_env.backup" s3://your-backup-bucket/
