#!/bin/bash

# Deploy Backup Script to EC2
# This script sets up the backup-invoiceninja.sh script on the EC2 instance

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
    exit 1
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

# Configuration
SCRIPT_NAME="backup-invoiceninja.sh"
TARGET_DIR="/opt/scripts"
SOURCE_DIR="/var/www/html/aws-deploy/scripts"

log "Setting up backup script on EC2..."

# Create target directory
log "Creating target directory: $TARGET_DIR"
sudo mkdir -p "$TARGET_DIR"

# Copy the script
log "Copying backup script..."
if [ -f "$SOURCE_DIR/$SCRIPT_NAME" ]; then
    sudo cp "$SOURCE_DIR/$SCRIPT_NAME" "$TARGET_DIR/"
    log "Script copied to $TARGET_DIR/$SCRIPT_NAME"
else
    error "Source script not found at $SOURCE_DIR/$SCRIPT_NAME"
fi

# Make it executable
log "Making script executable..."
sudo chmod +x "$TARGET_DIR/$SCRIPT_NAME"

# Create backup directory
log "Creating backup directory..."
sudo mkdir -p /var/backups/invoiceninja
sudo chown www-data:www-data /var/backups/invoiceninja
sudo chmod 755 /var/backups/invoiceninja

# Create symbolic link for easy access
log "Creating symbolic link..."
sudo ln -sf "$TARGET_DIR/$SCRIPT_NAME" /usr/local/bin/backup-invoiceninja

# Set up cron job (optional)
log "Setting up daily cron job..."
CRON_JOB="0 2 * * * /opt/scripts/backup-invoiceninja.sh > /var/log/backup-invoiceninja.log 2>&1"

# Check if cron job already exists
if ! crontab -l 2>/dev/null | grep -q "backup-invoiceninja.sh"; then
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    log "Cron job added for daily backup at 2:00 AM"
else
    warn "Cron job already exists"
fi

# Create log file
log "Creating log file..."
sudo touch /var/log/backup-invoiceninja.log
sudo chown www-data:www-data /var/log/backup-invoiceninja.log
sudo chmod 644 /var/log/backup-invoiceninja.log

# Test script location
log "Testing script location..."
if [ -x "$TARGET_DIR/$SCRIPT_NAME" ]; then
    log "Script is executable at $TARGET_DIR/$SCRIPT_NAME"
else
    error "Script is not executable"
fi

# Display usage information
log "=== SETUP COMPLETE ==="
log "Script location: $TARGET_DIR/$SCRIPT_NAME"
log "Symbolic link: /usr/local/bin/backup-invoiceninja"
log "Backup directory: /var/backups/invoiceninja"
log "Log file: /var/log/backup-invoiceninja.log"
log ""
log "Usage examples:"
log "  $TARGET_DIR/$SCRIPT_NAME [DB_PASSWORD]"
log "  backup-invoiceninja [DB_PASSWORD]"
log "  backup-invoiceninja [DB_PASSWORD] [S3_BUCKET]"
log ""
log "Cron job: Daily backup at 2:00 AM"
log "Manual test: backup-invoiceninja [your_db_password]" 