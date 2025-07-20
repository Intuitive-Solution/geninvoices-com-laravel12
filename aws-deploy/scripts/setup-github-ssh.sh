#!/bin/bash

# GitHub SSH Setup Script
# This script sets up SSH keys for GitHub access

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
SSH_KEY_PATH="/home/ec2-user/.ssh/id_rsa"
SSH_CONFIG_PATH="/home/ec2-user/.ssh/config"
GITHUB_HOST="github.com"

log "Setting up GitHub SSH access..."

# Check if running as ec2-user
if [ "$USER" != "ec2-user" ]; then
    error "This script must be run as ec2-user"
fi

# Create SSH directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generate SSH key if it doesn't exist
if [ ! -f "$SSH_KEY_PATH" ]; then
    log "Generating SSH key for GitHub..."
    ssh-keygen -t rsa -b 4096 -f "$SSH_KEY_PATH" -N "" -C "ec2-user@$(hostname)"
    chmod 600 "$SSH_KEY_PATH"
    chmod 644 "${SSH_KEY_PATH}.pub"
    log "SSH key generated: $SSH_KEY_PATH"
else
    log "SSH key already exists: $SSH_KEY_PATH"
fi

# Configure SSH for GitHub
log "Configuring SSH for GitHub..."
cat > "$SSH_CONFIG_PATH" << EOF
Host github.com
    HostName github.com
    User git
    IdentityFile $SSH_KEY_PATH
    StrictHostKeyChecking no
    UserKnownHostsFile /dev/null
EOF

chmod 600 "$SSH_CONFIG_PATH"

# Add GitHub to known hosts
log "Adding GitHub to known hosts..."
ssh-keyscan -H github.com >> ~/.ssh/known_hosts 2>/dev/null

# Display public key
log "=== SSH PUBLIC KEY ==="
log "Copy this key to your GitHub account (Settings > SSH and GPG keys > New SSH key):"
echo
cat "${SSH_KEY_PATH}.pub"
echo

info "=== NEXT STEPS ==="
info "1. Copy the SSH public key above"
info "2. Go to GitHub.com > Settings > SSH and GPG keys"
info "3. Click 'New SSH key'"
info "4. Paste the key and give it a title"
info "5. Test the connection: ssh -T git@github.com"
info "6. Continue with deployment: ./deploy.sh --mode app-only"

log "GitHub SSH setup completed!" 