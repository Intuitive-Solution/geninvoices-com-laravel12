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

log "Starting Google Chrome installation for PDF generation..."

# Update system packages
log "Updating system packages..."
yum update -y

# Install dependencies for Chrome
log "Installing Chrome dependencies..."
yum install -y \
    alsa-lib \
    atk \
    cups-libs \
    gtk3 \
    ipa-gothic-fonts \
    libXcomposite \
    libXcursor \
    libXdamage \
    libXext \
    libXi \
    libXrandr \
    libXScrnSaver \
    libXtst \
    pango \
    xorg-x11-fonts-100dpi \
    xorg-x11-fonts-75dpi \
    xorg-x11-fonts-cyrillic \
    xorg-x11-fonts-misc \
    xorg-x11-fonts-Type1 \
    xorg-x11-utils \
    liberation-fonts \
    libappindicator-gtk3 \
    libxss1 \
    lsb \
    xdg-utils \
    wget \
    nss \
    nspr \
    ca-certificates \
    fonts-liberation \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 || true

# Install additional fonts for better PDF rendering
log "Installing additional fonts..."
yum install -y \
    google-noto-sans-fonts \
    google-noto-serif-fonts \
    google-noto-emoji-fonts \
    dejavu-fonts-common \
    dejavu-sans-fonts \
    dejavu-serif-fonts || true

# Download and install Google Chrome stable
log "Downloading Google Chrome..."
wget -q -O /tmp/google-chrome-stable_current_x86_64.rpm https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm

if [ ! -f /tmp/google-chrome-stable_current_x86_64.rpm ]; then
    error "Failed to download Google Chrome"
    exit 1
fi

log "Installing Google Chrome..."
yum install -y /tmp/google-chrome-stable_current_x86_64.rpm

# Verify installation
if command -v google-chrome &> /dev/null; then
    CHROME_VERSION=$(google-chrome --version)
    log "Google Chrome installed successfully: $CHROME_VERSION"
else
    error "Google Chrome installation failed"
    exit 1
fi

# Create a symlink for compatibility
ln -sf /usr/bin/google-chrome /usr/bin/google-chrome-stable 2>/dev/null || true

# Test Chrome in headless mode
log "Testing Chrome in headless mode..."
timeout 10 google-chrome \
    --headless \
    --no-sandbox \
    --disable-gpu \
    --disable-dev-shm-usage \
    --dump-dom \
    https://www.google.com > /dev/null 2>&1

if [ $? -eq 0 ] || [ $? -eq 124 ]; then
    log "Chrome headless test successful"
else
    warn "Chrome headless test may have issues, but continuing..."
fi

# Set proper permissions for nginx user
log "Setting permissions for nginx user..."
usermod -a -G audio,video nginx 2>/dev/null || true

# Create Chrome cache directory for nginx user
mkdir -p /var/www/.cache/google-chrome
chown -R nginx:nginx /var/www/.cache

# Clean up
rm -f /tmp/google-chrome-stable_current_x86_64.rpm

log "Google Chrome installation completed!"
log "Chrome path: /usr/bin/google-chrome"
log ""
log "Recommended .env settings for Invoice Ninja:"
echo "PDF_GENERATOR=snappdf"
echo "SNAPPDF_CHROMIUM_PATH=\"/usr/bin/google-chrome\""
echo "SNAPPDF_CHROMIUM_ARGUMENTS=\"--no-sandbox --disable-dev-shm-usage --disable-gpu --headless\""
echo ""
log "Note: You may need to restart your web server and PHP-FPM after installation"



