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

# Initialize git submodules if needed
if [ ! -f tools/zul-check-ports/check-ports ]; then
    echo "Initializing git submodules..."
    git submodule update --init --recursive
    echo ""
fi

# Check port availability and generate .env.ports
echo "Checking port availability..."
if ! ./docker/scripts/check-ports.sh; then
    echo ""
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Source the generated ports file
if [ -f .env.ports ]; then
    source .env.ports
    export WP_PORT
    export DB_PORT
    export PMA_PORT
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