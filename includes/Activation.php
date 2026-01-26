<?php
/**
 * Plugin activation/deactivation handling
 *
 * @package Zul\Weather
 */

namespace Zul\Weather;

class Activation
{
    public static function activate(): void
    {
        self::createTables();
        self::addCapabilities();
        self::setVersion();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        // No data removal - preserve user data
        flush_rewrite_rules();
    }

    private static function createTables(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'zul_weather_info';

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            location VARCHAR(255) NOT NULL,
            description TEXT NULL,
            latitude DECIMAL(10,7) NOT NULL,
            longitude DECIMAL(10,7) NOT NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            create_dt DATETIME NOT NULL,
            modified_dt DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_created_by (created_by)
        ) {$charsetCollate};";

        dbDelta($sql);
    }

    private static function addCapabilities(): void
    {
        Capabilities::addToRole('administrator');
    }

    private static function setVersion(): void
    {
        update_option('zul_weather_version', ZUL_WEATHER_VERSION);
    }

    public static function maybeUpgrade(): void
    {
        $installedVersion = get_option('zul_weather_version', '0');

        if (version_compare($installedVersion, ZUL_WEATHER_VERSION, '<')) {
            self::createTables();
            self::setVersion();
        }
    }
}
