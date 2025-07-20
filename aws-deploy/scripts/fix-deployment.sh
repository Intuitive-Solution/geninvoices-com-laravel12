#!/bin/bash

# Recovery Script for Invoice Ninja Deployment
# This script fixes common deployment issues and resumes the deployment

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

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root"
fi

log "Starting deployment recovery process..."

# Function to fix DNF cache issues
fix_dnf_cache() {
    log "Fixing DNF cache issues..."
    
    # Remove all cache files
    rm -rf /var/cache/dnf/*
    rm -rf /var/cache/yum/*
    
    # Clean DNF cache
    dnf clean all
    dnf clean expire-cache
    
    # Rebuild cache
    dnf makecache --refresh
    
    # Check for broken packages
    dnf check || warn "Some packages may have issues"
    
    log "✓ DNF cache fixed"
}

# Function to fix package conflicts
fix_package_conflicts() {
    log "Fixing package conflicts..."
    
    # Remove any conflicting packages
    dnf autoremove -y || warn "Autoremove failed"
    
    # Fix any broken dependencies
    dnf check || warn "Package check found issues"
    
    log "✓ Package conflicts resolved"
}

# Function to install packages with better error handling
install_packages() {
    local packages=("$@")
    
    for package in "${packages[@]}"; do
        log "Installing $package..."
        
        for attempt in {1..3}; do
            if dnf install -y "$package" --allowerasing --skip-broken; then
                log "✓ $package installed successfully"
                break
            else
                warn "$package installation failed (attempt $attempt/3)"
                if [ $attempt -lt 3 ]; then
                    log "Cleaning cache and retrying..."
                    dnf clean all
                    dnf makecache --refresh
                    sleep 5
                else
                    warn "Failed to install $package after 3 attempts, continuing..."
                fi
            fi
        done
    done
}

# Main recovery process
main() {
    log "=== DEPLOYMENT RECOVERY STARTED ==="
    
    # Step 1: Fix DNF cache
    fix_dnf_cache
    
    # Step 2: Fix package conflicts
    fix_package_conflicts
    
    # Step 3: Update system
    log "Updating system packages..."
    for attempt in {1..3}; do
        if dnf update -y --skip-broken; then
            log "✓ System updated successfully"
            break
        else
            warn "System update failed (attempt $attempt/3)"
            if [ $attempt -lt 3 ]; then
                fix_dnf_cache
                sleep 10
            else
                warn "System update failed, continuing with package installation..."
            fi
        fi
    done
    
    # Step 4: Install essential packages
    log "Installing essential packages..."
    install_packages "wget" "curl" "git" "unzip" "vim" "nano" "htop"
    
    # Step 5: Install PHP and extensions
    log "Installing PHP and extensions..."
    install_packages "php" "php-cli" "php-fpm" "php-mysqlnd" "php-xml" "php-gd" \
        "php-mbstring" "php-curl" "php-zip" "php-intl" "php-bcmath" \
        "php-opcache" "php-json" "php-dom" "php-fileinfo" "php-openssl" "php-pdo" "php-ctype"
    
    # Step 6: Install web server
    log "Installing Nginx..."
    install_packages "nginx"
    
    # Step 7: Install database
    log "Installing MariaDB..."
    install_packages "mariadb105-server" "mariadb105"
    
    # Step 8: Install development tools
    log "Installing development tools..."
    install_packages "nodejs" "npm"
    
    # Step 9: Install SSL tools
    log "Installing SSL tools..."
    install_packages "certbot" "python3-certbot-nginx"
    
    log "=== RECOVERY COMPLETED ==="
    log "You can now continue with the deployment by running:"
    log "  ./deploy.sh --mode app-only"
}

# Run main function
main "$@" 