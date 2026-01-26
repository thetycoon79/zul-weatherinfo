#!/bin/sh
#
# PHPUnit Test Runner Script
# Runs tests inside the PHPUnit container (composer image)
#

set -e

echo "=========================================="
echo "ZUL Weather Info - PHPUnit Tests"
echo "=========================================="

cd /app

# Check if composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

echo ""
echo "Running PHPUnit tests..."
echo ""

# Run PHPUnit with any passed arguments
./vendor/bin/phpunit "$@"

echo ""
echo "Tests completed!"
