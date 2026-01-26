<?php
/**
 * Uninstall handler - runs when plugin is deleted
 *
 * @package Zul\Weather
 */

// Exit if accessed directly or not via WordPress uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete options
delete_option('zul_weather_api_key');
delete_option('zul_weather_cache_minutes');
delete_option('zul_weather_version');

// Delete transients (cached weather data)
$wpdb->query(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_zul_weather_%' OR option_name LIKE '_transient_timeout_zul_weather_%'"
);

// Drop custom table
$table = $wpdb->prefix . 'zul_weather_info';
$wpdb->query("DROP TABLE IF EXISTS {$table}");

// Remove capabilities from administrator role
$role = get_role('administrator');
if ($role) {
    $role->remove_cap('zul_weather_manage');
    $role->remove_cap('zul_weather_view_admin');
}
