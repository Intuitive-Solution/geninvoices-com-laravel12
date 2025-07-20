#!/bin/bash

# Invoice Ninja Backup Script with S3 Upload
# This script creates database backups and uploads them to S3
# Usage: ./backup-invoiceninja.sh [DB_PASSWORD] [S3_BUCKET]

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
BACKUP_DIR="/var/backups/invoiceninja"
APP_DIR="/var/www/html"
S3_BUCKET=${2:-"demo.geninovices.com"}
S3_PREFIX="database-backups"
RETENTION_DAYS=30

# Database configuration - can be overridden by environment variables
DB_HOST=${DB_HOST:-"127.0.0.1"}
DB_PORT=${DB_PORT:-"3306"}
DB_NAME=${DB_NAME:-"invoiceninja"}
DB_USER=${DB_USER:-"invoiceninja"}
DB_PASSWORD=${1:-${DB_PASSWORD:-"apple1234"}}

# Validate parameters
if [ -z "$DB_PASSWORD" ]; then
    error "Database password is required as first parameter or set DB_PASSWORD environment variable"
fi

# Check if AWS CLI is installed
if ! command -v aws &> /dev/null; then
    error "AWS CLI is not installed. Please install it first."
fi

# Check if mysqldump is available
if ! command -v mysqldump &> /dev/null; then
    error "mysqldump is not installed. Please install MariaDB client tools."
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DATE_FOLDER=$(date +%Y/%m/%d)

log "Starting Invoice Ninja backup process..."
log "Database: $DB_NAME@$DB_HOST:$DB_PORT"
log "S3 Bucket: $S3_BUCKET"
log "S3 Prefix: $S3_PREFIX"

# Test database connection
log "Testing database connection..."
if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1;" "$DB_NAME" > /dev/null 2>&1; then
    error "Cannot connect to database. Please check your credentials and connection."
fi
log "Database connection successful"

# Database backup
log "Creating database backup..."
DB_BACKUP_FILE="$BACKUP_DIR/invoiceninja_db_$TIMESTAMP.sql"
COMPRESSED_DB_FILE="${DB_BACKUP_FILE}.gz"

# Create database dump with additional options for better compatibility
mysqldump \
    -h "$DB_HOST" \
    -P "$DB_PORT" \
    -u "$DB_USER" \
    -p"$DB_PASSWORD" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-database \
    --add-drop-table \
    --add-drop-trigger \
    --add-locks \
    --comments \
    --create-options \
    --dump-date \
    --extended-insert \
    --force \
    --hex-blob \
    --lock-tables=false \
    --set-charset \
    "$DB_NAME" > "$DB_BACKUP_FILE"

# Check if dump was successful
if [ ! -s "$DB_BACKUP_FILE" ]; then
    error "Database dump failed or is empty"
fi

# Compress the backup
log "Compressing database backup..."
gzip "$DB_BACKUP_FILE"
log "Database backup created: $COMPRESSED_DB_FILE"

# Backup .env file
log "Creating .env file backup..."
ENV_BACKUP_FILE="$BACKUP_DIR/invoiceninja_env_$TIMESTAMP.env"
if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$ENV_BACKUP_FILE"
    log ".env backup created: $ENV_BACKUP_FILE"
else
    warn ".env file not found at $APP_DIR/.env"
    # Create an empty .env backup file with a note
    echo "# .env file not found during backup at $(date)" > "$ENV_BACKUP_FILE"
fi

# Get file sizes
BACKUP_SIZE=$(du -h "$COMPRESSED_DB_FILE" | cut -f1)
ENV_SIZE=$(du -h "$ENV_BACKUP_FILE" | cut -f1)
log "Database backup size: $BACKUP_SIZE"
log ".env backup size: $ENV_SIZE"

# Upload to S3
log "Uploading database backup to S3..."
DB_S3_KEY="$S3_PREFIX/$DATE_FOLDER/invoiceninja_db_$TIMESTAMP.sql.gz"

# Upload database backup with metadata
aws s3 cp "$COMPRESSED_DB_FILE" "s3://$S3_BUCKET/$DB_S3_KEY" \
    --metadata "backup-date=$TIMESTAMP,application=invoiceninja,type=database" \
    --storage-class STANDARD_IA

if [ $? -eq 0 ]; then
    log "Successfully uploaded database backup to s3://$S3_BUCKET/$DB_S3_KEY"
else
    error "Failed to upload database backup to S3"
fi

# Upload .env file to S3
log "Uploading .env backup to S3..."
ENV_S3_KEY="$S3_PREFIX/$DATE_FOLDER/invoiceninja_env_$TIMESTAMP.env"

aws s3 cp "$ENV_BACKUP_FILE" "s3://$S3_BUCKET/$ENV_S3_KEY" \
    --metadata "backup-date=$TIMESTAMP,application=invoiceninja,type=env" \
    --storage-class STANDARD_IA

if [ $? -eq 0 ]; then
    log "Successfully uploaded .env backup to s3://$S3_BUCKET/$ENV_S3_KEY"
else
    error "Failed to upload .env backup to S3"
fi

# Create backup manifest
log "Creating backup manifest..."
MANIFEST_FILE="$BACKUP_DIR/backup_manifest_$TIMESTAMP.txt"
cat > "$MANIFEST_FILE" << EOF
Invoice Ninja Backup Manifest
============================
Generated: $(date)
Hostname: $(hostname)
Server IP: $(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "Unknown")

Database Information:
- Host: $DB_HOST
- Port: $DB_PORT
- Database: $DB_NAME
- User: $DB_USER

Database Backup Information:
- Local File: $COMPRESSED_DB_FILE
- S3 Location: s3://$S3_BUCKET/$DB_S3_KEY
- Size: $BACKUP_SIZE
- Timestamp: $TIMESTAMP

Environment Backup Information:
- Local File: $ENV_BACKUP_FILE
- S3 Location: s3://$S3_BUCKET/$ENV_S3_KEY
- Size: $ENV_SIZE
- Timestamp: $TIMESTAMP

S3 Upload Details:
- Bucket: $S3_BUCKET
- Storage Class: STANDARD_IA
- Retention: $RETENTION_DAYS days

Restore Commands:
Database: gunzip -c $COMPRESSED_DB_FILE | mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p[PASSWORD] $DB_NAME
Environment: cp $ENV_BACKUP_FILE /var/www/html/.env
EOF

# Upload manifest to S3
MANIFEST_S3_KEY="$S3_PREFIX/$DATE_FOLDER/backup_manifest_$TIMESTAMP.txt"
aws s3 cp "$MANIFEST_FILE" "s3://$S3_BUCKET/$MANIFEST_S3_KEY" \
    --metadata "backup-date=$TIMESTAMP,application=invoiceninja,type=manifest"

log "Backup manifest uploaded to s3://$S3_BUCKET/$MANIFEST_S3_KEY"

# Cleanup old local backups
log "Cleaning up old local backups (older than $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "*.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*.env" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*.txt" -mtime +$RETENTION_DAYS -delete
log "Old local backups cleaned up"

# Optional: Cleanup old S3 backups (uncomment if needed)
# log "Cleaning up old S3 backups (older than $RETENTION_DAYS days)..."
# aws s3 ls "s3://$S3_BUCKET/$S3_PREFIX/" --recursive | \
#     awk -v cutoff=\$(date -d "$RETENTION_DAYS days ago" +%Y-%m-%d) '\$1 < cutoff {print \$4}' | \
#     xargs -I {} aws s3 rm "s3://$S3_BUCKET/{}" 2>/dev/null || warn "Some old S3 backups may not have been cleaned up"

# Display backup summary
log "=== BACKUP SUMMARY ==="
log "Database backup: $COMPRESSED_DB_FILE"
log "Database S3: s3://$S3_BUCKET/$DB_S3_KEY"
log "Environment backup: $ENV_BACKUP_FILE"
log "Environment S3: s3://$S3_BUCKET/$ENV_S3_KEY"
log "Manifest: $MANIFEST_FILE"
log "Manifest S3: s3://$S3_BUCKET/$MANIFEST_S3_KEY"
log "Database size: $BACKUP_SIZE"
log "Environment size: $ENV_SIZE"
log "Retention: $RETENTION_DAYS days"

# Verify S3 uploads
log "Verifying S3 uploads..."
if aws s3 ls "s3://$S3_BUCKET/$DB_S3_KEY" > /dev/null 2>&1; then
    log "Database S3 upload verification successful"
else
    warn "Database S3 upload verification failed"
fi

if aws s3 ls "s3://$S3_BUCKET/$ENV_S3_KEY" > /dev/null 2>&1; then
    log "Environment S3 upload verification successful"
else
    warn "Environment S3 upload verification failed"
fi

log "Invoice Ninja backup completed successfully!" 