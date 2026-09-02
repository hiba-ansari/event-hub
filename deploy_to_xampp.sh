#!/bin/bash

# Define source and destination directories
SOURCE_DIR="/Users/hibiscus/event-hub"
DEST_DIR="/Applications/XAMPP/xamppfiles/htdocs/event-hub"

echo "Deploying files from $SOURCE_DIR to $DEST_DIR..."

# Copy all files and directories recursively, preserving attributes
cp -r "$SOURCE_DIR"/. "$DEST_DIR"

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