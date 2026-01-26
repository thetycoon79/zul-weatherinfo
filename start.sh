#!/bin/bash
#
# ZUL Weather Info - Docker Environment Starter
# Starts the Docker environment and runs WordPress setup
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=========================================="
echo "ZUL Weather Info - Docker Environment"
echo "=========================================="
echo ""

# Load .env file if exists
if [ -f .env ]; then
    echo "Loading configuration from .env file..."
    export $(cat .env | grep -v '^#' | xargs)
fi

# Set defaults
export WP_PORT=${WP_PORT:-8080}
export DB_PORT=${DB_PORT:-3307}
export PMA_PORT=${PMA_PORT:-8081}

# Check port availability
echo "Checking port availability..."
if ! ./docker/scripts/check-ports.sh; then
    echo ""
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo "Starting Docker containers..."
docker-compose up -d wordpress db

echo ""
echo "Waiting for services to be ready..."
sleep 5

echo ""
echo "Running WordPress setup..."
docker-compose run --rm wpcli sh /scripts/install.sh

echo ""
echo "=========================================="
echo "Environment is ready!"
echo "=========================================="
echo ""
echo "Services:"
echo "  WordPress:   http://localhost:$WP_PORT"
echo "  Admin:       http://localhost:$WP_PORT/wp-admin (admin/admin)"
echo "  MySQL:       localhost:$DB_PORT"
echo ""
echo "Commands:"
echo "  Stop:        docker-compose down"
echo "  Logs:        docker-compose logs -f"
echo "  WP-CLI:      docker-compose run --rm wpcli wp <command>"
echo "  PHPUnit:     docker-compose run --rm phpunit /scripts/run-tests.sh"
echo "  phpMyAdmin:  docker-compose --profile debug up -d phpmyadmin"
echo ""
