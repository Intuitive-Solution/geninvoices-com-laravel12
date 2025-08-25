#!/bin/bash

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root or with sudo"
   exit 1
fi

log "Fixing PDF generation issues on EC2..."

# 1. Increase system limits for Chrome
log "Configuring system limits..."
cat > /etc/security/limits.d/99-chrome.conf << 'EOF'
* soft nofile 65536
* hard nofile 65536
* soft nproc 32768
* hard nproc 32768
nginx soft nofile 65536
nginx hard nofile 65536
nginx soft nproc 32768
nginx hard nproc 32768
EOF

# 2. Increase shared memory
log "Increasing shared memory..."
mount -o remount,size=2G /dev/shm

# Make it permanent
if ! grep -q "tmpfs.*\/dev\/shm.*size=2G" /etc/fstab; then
    echo "tmpfs /dev/shm tmpfs defaults,size=2G 0 0" >> /etc/fstab
fi

# 3. Create swap if not exists (helps with memory issues)
log "Checking swap space..."
if [ $(swapon -s | wc -l) -eq 0 ]; then
    log "Creating 2GB swap file..."
    dd if=/dev/zero of=/swapfile bs=1M count=2048
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo "/swapfile swap swap defaults 0 0" >> /etc/fstab
else
    log "Swap already configured"
fi

# 4. Install missing libraries
log "Installing additional libraries..."
yum install -y \
    mesa-libGL \
    mesa-libEGL \
    libdrm \
    libgbm \
    alsa-lib-devel \
    gtk3-devel \
    nss-devel

# 5. Configure Chrome cache and temp directories
log "Setting up Chrome directories..."
mkdir -p /var/www/.cache/google-chrome
mkdir -p /var/www/.config/google-chrome
mkdir -p /var/www/.local/share
mkdir -p /tmp/chrome-pdf
chown -R nginx:nginx /var/www/.cache
chown -R nginx:nginx /var/www/.config
chown -R nginx:nginx /var/www/.local
chown -R nginx:nginx /tmp/chrome-pdf
chmod 755 /tmp/chrome-pdf

# 6. Update .env file with optimized settings
ENV_FILE="/var/www/html/.env"
if [ -f "$ENV_FILE" ]; then
    log "Updating .env file with optimized PDF settings..."
    
    # Backup current .env
    cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Update PDF generator settings
    sed -i 's/^PDF_GENERATOR=.*/PDF_GENERATOR=snappdf/' "$ENV_FILE"
    
    # Update Chrome path
    if grep -q "^SNAPPDF_CHROMIUM_PATH=" "$ENV_FILE"; then
        sed -i 's|^SNAPPDF_CHROMIUM_PATH=.*|SNAPPDF_CHROMIUM_PATH="/usr/bin/google-chrome"|' "$ENV_FILE"
    else
        echo 'SNAPPDF_CHROMIUM_PATH="/usr/bin/google-chrome"' >> "$ENV_FILE"
    fi
    
    # Update Chrome arguments with optimized settings for EC2
    CHROME_ARGS="--no-sandbox --disable-dev-shm-usage --disable-gpu --headless --disable-software-rasterizer --disable-extensions --disable-web-security --disable-features=VizDisplayCompositor --disable-setuid-sandbox --single-process --no-zygote --disable-background-timer-throttling --disable-backgrounding-occluded-windows --disable-renderer-backgrounding --disable-features=TranslateUI --disable-ipc-flooding-protection --disable-default-apps --no-first-run --no-default-browser-check --disable-blink-features=AutomationControlled --window-size=1920,1080"
    
    if grep -q "^SNAPPDF_CHROMIUM_ARGUMENTS=" "$ENV_FILE"; then
        sed -i "s|^SNAPPDF_CHROMIUM_ARGUMENTS=.*|SNAPPDF_CHROMIUM_ARGUMENTS=\"$CHROME_ARGS\"|" "$ENV_FILE"
    else
        echo "SNAPPDF_CHROMIUM_ARGUMENTS=\"$CHROME_ARGS\"" >> "$ENV_FILE"
    fi
    
    # Increase timeout to 120 seconds
    if grep -q "^SNAPPDF_TIMEOUT=" "$ENV_FILE"; then
        sed -i 's/^SNAPPDF_TIMEOUT=.*/SNAPPDF_TIMEOUT=120000/' "$ENV_FILE"
    else
        echo 'SNAPPDF_TIMEOUT=120000' >> "$ENV_FILE"
    fi
    
    log ".env file updated successfully"
else
    warn ".env file not found at $ENV_FILE"
fi

# 7. Update PHP configuration for longer timeouts
log "Updating PHP configuration..."
PHP_INI="/etc/php.ini"
if [ -f "$PHP_INI" ]; then
    sed -i 's/^max_execution_time.*/max_execution_time = 300/' "$PHP_INI"
    sed -i 's/^memory_limit.*/memory_limit = 512M/' "$PHP_INI"
fi

PHP_FPM_CONF="/etc/php-fpm.d/www.conf"
if [ -f "$PHP_FPM_CONF" ]; then
    if ! grep -q "request_terminate_timeout" "$PHP_FPM_CONF"; then
        echo "request_terminate_timeout = 300" >> "$PHP_FPM_CONF"
    else
        sed -i 's/^request_terminate_timeout.*/request_terminate_timeout = 300/' "$PHP_FPM_CONF"
    fi
fi

# 8. Clear Laravel cache
log "Clearing Laravel cache..."
cd /var/www/html
sudo -u nginx php artisan config:clear
sudo -u nginx php artisan cache:clear

# 9. Restart services
log "Restarting services..."
systemctl restart php-fpm
systemctl restart nginx

# 10. Test Chrome
log "Testing Chrome installation..."
if command -v google-chrome &> /dev/null; then
    CHROME_VERSION=$(google-chrome --version)
    log "Chrome version: $CHROME_VERSION"
    
    # Test headless PDF generation
    log "Testing PDF generation..."
    echo "<html><body><h1>Test PDF</h1></body></html>" > /tmp/test.html
    timeout 30 google-chrome \
        --headless \
        --no-sandbox \
        --disable-dev-shm-usage \
        --disable-gpu \
        --print-to-pdf=/tmp/test.pdf \
        /tmp/test.html 2>/dev/null
    
    if [ -f /tmp/test.pdf ]; then
        log "PDF generation test successful!"
        rm -f /tmp/test.pdf /tmp/test.html
    else
        warn "PDF generation test failed, but Chrome is installed"
    fi
else
    error "Chrome is not installed. Please run install-chrome.sh first"
    exit 1
fi

log "PDF generation fix completed!"
log ""
log "Next steps:"
log "1. Test PDF generation in Invoice Ninja"
log "2. Monitor /var/www/html/storage/logs/laravel.log for any errors"
log "3. If issues persist, check memory usage with 'free -h'"



