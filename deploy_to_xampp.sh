#!/bin/bash

# Define source and destination directories
SOURCE_DIR="/Users/hibiscus/event-hub"
DEST_DIR="/Applications/XAMPP/xamppfiles/htdocs/event-hub"

echo "Deploying files from $SOURCE_DIR to $DEST_DIR..."

# Use rsync to copy files, excluding hidden files/directories like .git
# -a: archive mode (preserves permissions, times, etc.)
# -v: verbose output
# --delete: delete files in dest that are not in source (optional, use carefully)
# --exclude: exclude hidden files/dirs starting with .
rsync -av --exclude '.*' "$SOURCE_DIR/" "$DEST_DIR/"

# Set appropriate permissions for PHP files (optional, but recommended)
find "$DEST_DIR" -name "*.php" -exec chmod 644 {} \;

# Set appropriate permissions for directories (optional, but recommended)
find "$DEST_DIR" -type d -exec chmod 755 {} \;

# Set appropriate permissions for other common file types (optional)
find "$DEST_DIR" -name "*.js" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.css" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.html" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.png" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.jpg" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.jpeg" -exec chmod 644 {} \;
find "$DEST_DIR" -name "*.gif" -exec chmod 644 {} \;

echo "Deployment complete."