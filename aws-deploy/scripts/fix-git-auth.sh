#!/bin/bash

# Git Authentication Fix Script
# This script fixes Git authentication issues during deployment

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
APP_DIR="/var/www/html"
BACKUP_DIR="/home/ec2-user/backups/invoiceninja"
BRANCH=${1:-"master"}
REPO_URL="https://github.com/Intuitive-Solution/geninvoices-com-laravel12.git"

log "Fixing Git authentication and deploying application..."

# Check if running as ec2-user
if [ "$USER" != "ec2-user" ]; then
    error "This script must be run as ec2-user"
fi

# Create necessary directories
mkdir -p "$BACKUP_DIR"
mkdir -p "$APP_DIR"

# Clean up any existing failed clone attempts
if [ -d "$APP_DIR" ] && [ "$(ls -A $APP_DIR)" ]; then
    warn "Directory not empty, moving existing files to backup"
    mv "$APP_DIR" "$BACKUP_DIR/old-html-$(date +%Y%m%d-%H%M%S)"
    mkdir -p "$APP_DIR"
fi

# Clone the repository using public URL
log "Cloning Invoice Ninja repository (public)..."
if git clone "$REPO_URL" "$APP_DIR"; then
    log "✓ Repository cloned successfully"
    cd "$APP_DIR"
    
    # Checkout the specified branch
    if git checkout "$BRANCH" 2>/dev/null; then
        log "✓ Checked out branch: $BRANCH"
    else
        warn "Branch $BRANCH not found, using default branch"
        git checkout $(git symbolic-ref refs/remotes/origin/HEAD | sed 's@^refs/remotes/origin/@@')
    fi
else
    error "Failed to clone repository"
fi

# Set proper ownership
chown -R ec2-user:ec2-user "$APP_DIR"

log "✓ Git authentication issue resolved and repository cloned"
log "Application directory: $APP_DIR"
log "Branch: $(git branch --show-current)"

info "=== NEXT STEPS ==="
info "Repository has been cloned successfully."
info "You can now continue with the application setup." 