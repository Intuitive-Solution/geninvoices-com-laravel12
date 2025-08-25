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

# Switch to nginx user for Git operations to avoid permission issues
log "Switching to nginx user for Git operations..."
sudo -u nginx bash << 'NGINX_GIT_SCRIPT'
cd /var/www/html

# Ensure we're on the correct branch and pull latest changes
echo "Pulling latest changes from repository..."
git checkout $BRANCH

# Force reset to discard any local changes that might conflict
echo "Discarding local changes to ensure clean pull..."
git reset --hard HEAD
git clean -fd

# Now pull the latest changes
git pull origin $BRANCH

# Clean any remaining untracked files (preserve .env)
echo "Cleaning remaining untracked files..."
git clean -fd

# Verify we have the latest commit
echo "Current commit: $(git rev-parse HEAD)"
echo "Latest remote commit: $(git rev-parse origin/$BRANCH 2>/dev/null || echo 'Branch not found')"

echo "✓ Successfully updated repository from remote"
NGINX_GIT_SCRIPT

# Verify we have the latest changes
log "Verifying latest changes..."
sudo -u nginx bash << 'VERIFY_SCRIPT'
cd /var/www/html
echo "Current commit hash: $(git rev-parse HEAD)"
echo "Latest remote commit: $(git rev-parse origin/$BRANCH 2>/dev/null || echo 'Branch not found')"
echo "Last commit message: $(git log -1 --pretty=format:'%s')"
echo "Last commit date: $(git log -1 --pretty=format:'%cd')"
VERIFY_SCRIPT

# Set proper permissions BEFORE Composer install
log "Setting file permissions for Composer..."

# Create necessary directories first
sudo mkdir -p $APP_DIR/storage/app/public
sudo mkdir -p $APP_DIR/storage/framework/cache
sudo mkdir -p $APP_DIR/storage/framework/sessions
sudo mkdir -p $APP_DIR/storage/framework/views
sudo mkdir -p $APP_DIR/storage/logs
sudo mkdir -p $APP_DIR/bootstrap/cache

# Set ownership and permissions
sudo chown -R nginx:nginx $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# Install Composer dependencies as nginx user
log "Installing Composer dependencies..."
sudo -u nginx composer install --no-dev --optimize-autoloader --no-interaction

# Run database migrations
log "Running database migrations..."
sudo -u nginx php artisan migrate --force

# Clear all caches
log "Clearing application caches..."
sudo -u nginx php artisan config:clear
sudo -u nginx php artisan route:clear
sudo -u nginx php artisan view:clear
sudo -u nginx php artisan cache:clear

# Optimize application for production
log "Optimizing application for production..."
sudo -u nginx php artisan config:cache
sudo -u nginx php artisan route:cache
sudo -u nginx php artisan view:cache

# Set proper permissions
log "Setting file permissions..."

# Ensure directories exist
sudo mkdir -p $APP_DIR/storage/app/public
sudo mkdir -p $APP_DIR/storage/framework/cache
sudo mkdir -p $APP_DIR/storage/framework/sessions
sudo mkdir -p $APP_DIR/storage/framework/views
sudo mkdir -p $APP_DIR/storage/logs
sudo mkdir -p $APP_DIR/bootstrap/cache

# Set ownership and permissions
sudo chown -R nginx:nginx $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

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

if sudo -u nginx php artisan --version >/dev/null 2>&1; then
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