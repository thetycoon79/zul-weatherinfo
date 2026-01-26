#!/bin/bash
#
# WordPress Installation Script
# Runs via WP-CLI container to set up WordPress and activate the plugin
#

set -e

echo "=========================================="
echo "ZUL Weather Info - WordPress Setup"
echo "=========================================="

# Wait for MySQL to be ready using PHP
echo "Waiting for MySQL to be ready..."
sleep 5

MAX_TRIES=30
COUNT=0
until php -r "\$c = @new mysqli('db', 'wordpress', 'wordpress', 'wordpress'); if (\$c->connect_error) exit(1);" 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "Error: MySQL connection timeout after $MAX_TRIES attempts"
        exit 1
    fi
    echo "MySQL not ready yet. Retrying in 2 seconds... ($COUNT/$MAX_TRIES)"
    sleep 2
done

echo "MySQL is ready!"

# Wait for WordPress files to be ready
echo "Waiting for WordPress files..."
COUNT=0
while [ ! -f /var/www/html/wp-config.php ]; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge 30 ]; then
        echo "Error: wp-config.php not found. WordPress container may not be ready."
        exit 1
    fi
    echo "Waiting for wp-config.php... ($COUNT/30)"
    sleep 2
done

echo "WordPress files ready!"

# Check if WordPress is already installed
if wp core is-installed --allow-root 2>/dev/null; then
    echo "WordPress is already installed."
else
    echo "Installing WordPress..."

    wp core install \
        --url="http://localhost:${WP_PORT:-8084}" \
        --title="ZUL Weather Info Demo" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@example.com \
        --skip-email \
        --allow-root

    echo "WordPress installed successfully!"
fi

# Update WordPress settings
echo "Configuring WordPress settings..."
wp option update blogdescription "Weather Info Plugin Demo Site" --allow-root
wp option update timezone_string "UTC" --allow-root
wp option update permalink_structure "/%postname%/" --allow-root

# Activate the plugin
echo "Activating ZUL Weather Info plugin..."
if wp plugin is-installed zul-weatherinfo --allow-root 2>/dev/null; then
    wp plugin activate zul-weatherinfo --allow-root
    echo "Plugin activated!"
else
    echo "Warning: Plugin not found. Make sure the volume is mounted correctly."
    echo "Checking plugin directory..."
    ls -la /var/www/html/wp-content/plugins/ || true
fi

# Create a demo page with shortcode
echo "Creating demo page..."
if ! wp post list --post_type=page --name=weather-demo --format=ids --allow-root 2>/dev/null | grep -q .; then
    wp post create \
        --post_type=page \
        --post_title="Weather Demo" \
        --post_content='<h2>Weather Widget Demo</h2>
<p>This page demonstrates the ZUL Weather Info shortcode.</p>
<p>Add a location in the admin panel, then use the shortcode below:</p>
<pre>[zul_weather id="1"]</pre>
<p>Once you have created a location, the weather widget will appear here:</p>
[zul_weather id="1"]' \
        --post_status=publish \
        --allow-root
    echo "Demo page created!"
else
    echo "Demo page already exists."
fi

# Flush rewrite rules
wp rewrite flush --allow-root 2>/dev/null || true

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "WordPress Admin:"
echo "  URL:      http://localhost:${WP_PORT:-8084}/wp-admin"
echo "  Username: admin"
echo "  Password: admin"
echo ""
echo "Plugin Admin:"
echo "  Weather Info: http://localhost:${WP_PORT:-8084}/wp-admin/admin.php?page=zul-weather"
echo "  Settings:     http://localhost:${WP_PORT:-8084}/wp-admin/admin.php?page=zul-weather-settings"
echo ""
echo "Demo Page:"
echo "  http://localhost:${WP_PORT:-8084}/weather-demo/"
echo ""
