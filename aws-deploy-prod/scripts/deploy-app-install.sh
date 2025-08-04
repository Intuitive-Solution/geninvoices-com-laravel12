#!/bin/bash

# Invoice Ninja Application Code Installation Script
# This script installs only the application code without modifying server configuration

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

# Configuration
REPO_URL="https://github.com/Intuitive-Solution/geninvoices-com-laravel12.git"
APP_DIR="/var/www/html"
BRANCH=${1:-"master"}

log "Starting Invoice Ninja code installation..."
log "Branch: $BRANCH"
log "App Directory: $APP_DIR"

# Navigate to application directory
cd $APP_DIR

# Update repository and fetch new changes
log "Fetching new changes from repository..."
git fetch origin
git checkout $BRANCH

# Force reset to match remote branch exactly (discard all local changes)
log "Discarding local changes and forcing update from remote..."
git reset --hard origin/$BRANCH

# Clean untracked files
log "Cleaning untracked files..."
git clean -fd

log "✓ Successfully updated repository from remote"

# Install Composer dependencies
log "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run database migrations
log "Running database migrations..."
php artisan migrate --force

# Clear all caches
log "Clearing application caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize application for production
log "Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
log "Setting file permissions..."
chown -R ec2-user:ec2-user $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Restart services
log "Restarting services..."
sudo systemctl restart nginx
sudo systemctl restart php-fpm

# Verify installation
log "Verifying installation..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
    log "✓ Web server is responding"
else
    warn "Web server may not be responding correctly"
fi

if php artisan --version >/dev/null 2>&1; then
    log "✓ Laravel application is working"
else
    error "Laravel application is not working correctly"
fi

# Display installation summary
log "=== INSTALLATION SUMMARY ==="
log "Application: Invoice Ninja"
log "Version/Branch: $BRANCH"
log "Directory: $APP_DIR"
log "Environment: production"
log "Status: INSTALLED"

log "Invoice Ninja code installation completed successfully!" 