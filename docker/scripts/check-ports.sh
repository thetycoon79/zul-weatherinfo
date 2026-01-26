#!/bin/bash
#
# Port Availability Checker
# Checks if required ports are available and suggests alternatives
#

set -e

# Default ports
WP_PORT=${WP_PORT:-8080}
DB_PORT=${DB_PORT:-3307}
PMA_PORT=${PMA_PORT:-8081}

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=========================================="
echo "Checking Port Availability"
echo "=========================================="
echo ""

# Function to check if a port is in use
check_port() {
    local port=$1
    local name=$2

    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo -e "${RED}[BUSY]${NC} Port $port ($name) is already in use"
        return 1
    else
        echo -e "${GREEN}[FREE]${NC} Port $port ($name) is available"
        return 0
    fi
}

# Function to find next available port
find_available_port() {
    local start_port=$1
    local port=$start_port

    while lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; do
        port=$((port + 1))
        if [ $port -gt $((start_port + 100)) ]; then
            echo "0"
            return
        fi
    done

    echo $port
}

# Track if any ports are busy
PORTS_BUSY=0

# Check WordPress port
if ! check_port $WP_PORT "WordPress"; then
    PORTS_BUSY=1
    NEW_PORT=$(find_available_port $WP_PORT)
    if [ "$NEW_PORT" != "0" ]; then
        echo -e "  ${YELLOW}Suggestion:${NC} Use WP_PORT=$NEW_PORT"
    fi
fi

# Check MySQL port
if ! check_port $DB_PORT "MySQL"; then
    PORTS_BUSY=1
    NEW_PORT=$(find_available_port $DB_PORT)
    if [ "$NEW_PORT" != "0" ]; then
        echo -e "  ${YELLOW}Suggestion:${NC} Use DB_PORT=$NEW_PORT"
    fi
fi

# Check phpMyAdmin port
if ! check_port $PMA_PORT "phpMyAdmin"; then
    PORTS_BUSY=1
    NEW_PORT=$(find_available_port $PMA_PORT)
    if [ "$NEW_PORT" != "0" ]; then
        echo -e "  ${YELLOW}Suggestion:${NC} Use PMA_PORT=$NEW_PORT"
    fi
fi

echo ""

if [ $PORTS_BUSY -eq 1 ]; then
    echo -e "${YELLOW}Some ports are in use.${NC}"
    echo ""
    echo "You can specify alternative ports when starting:"
    echo "  WP_PORT=8082 DB_PORT=3308 ./start.sh"
    echo ""
    echo "Or set them in a .env file:"
    echo "  echo 'WP_PORT=8082' > .env"
    echo "  echo 'DB_PORT=3308' >> .env"
    echo ""
    exit 1
else
    echo -e "${GREEN}All ports are available!${NC}"
    exit 0
fi
