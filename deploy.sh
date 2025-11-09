#!/bin/bash
# Manual Deploy Script for Hostinger
# Upload this to your server and run it when needed

echo "==================================="
echo "NSFS Manual Deploy Script"
echo "==================================="
echo ""

# Navigate to your project directory
cd /home2/workuidy/public_html/north_super_fast_service/application || exit 1
echo "✓ Changed to project directory"

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"

# Fetch latest changes
echo ""
echo "Fetching latest changes from GitHub..."
git fetch origin

# Show what will be updated
echo ""
echo "Changes to be pulled:"
git log HEAD..origin/main --oneline

# Pull latest changes
echo ""
echo "Pulling changes..."
git pull origin main

# Check status
if [ $? -eq 0 ]; then
    echo ""
    echo "==================================="
    echo "✓ Deploy successful!"
    echo "==================================="
    echo ""
    echo "Latest commit:"
    git log -1 --oneline
else
    echo ""
    echo "==================================="
    echo "✗ Deploy failed!"
    echo "==================================="
    echo "Please check for merge conflicts or permission issues"
    exit 1
fi

echo ""
echo "Done!"
