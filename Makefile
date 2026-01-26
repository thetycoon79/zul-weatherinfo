# ZUL Weather Info - Makefile
# Convenience commands for development

.PHONY: start stop restart logs shell test install clean help

# Default target
help:
	@echo "ZUL Weather Info - Development Commands"
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  start      Start Docker environment"
	@echo "  stop       Stop Docker environment"
	@echo "  restart    Restart Docker environment"
	@echo "  logs       View container logs"
	@echo "  shell      Open shell in WordPress container"
	@echo "  wpcli      Run WP-CLI command (usage: make wpcli CMD='plugin list')"
	@echo "  test       Run PHPUnit tests"
	@echo "  install    Install Composer dependencies"
	@echo "  clean      Remove Docker volumes and data"
	@echo "  ports      Check port availability"
	@echo ""

# Start the Docker environment
start:
	@./start.sh

# Stop the Docker environment
stop:
	@docker-compose down

# Restart the Docker environment
restart: stop start

# View logs
logs:
	@docker-compose logs -f

# Open shell in WordPress container
shell:
	@docker-compose exec wordpress bash

# Run WP-CLI command
wpcli:
	@docker-compose run --rm wpcli wp $(CMD) --allow-root

# Run PHPUnit tests
test:
	@docker-compose run --rm phpunit /scripts/run-tests.sh

# Install Composer dependencies locally
install:
	@composer install

# Check port availability
ports:
	@./docker/scripts/check-ports.sh

# Clean up Docker volumes and data
clean:
	@echo "This will remove all Docker volumes and data for this project."
	@read -p "Are you sure? (y/N) " confirm && [ "$$confirm" = "y" ] || exit 1
	@docker-compose down -v
	@echo "Cleaned up!"

# Start with phpMyAdmin
debug:
	@docker-compose --profile debug up -d
	@echo "phpMyAdmin available at http://localhost:$${PMA_PORT:-8081}"
