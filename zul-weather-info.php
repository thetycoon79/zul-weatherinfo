<?php
/**
 * Plugin Name: ZUL Weather Info
 * Plugin URI: https://example.com/zul-weather
 * Description: Display weather information for configured locations using OpenWeatherMap API
 * Version: 1.0.0
 * Author: ZUL
 * Author URI: https://example.com
 * Text Domain: zul-weather
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Zul\Weather
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ZUL_WEATHER_VERSION', '1.0.0');
define('ZUL_WEATHER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZUL_WEATHER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZUL_WEATHER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once ZUL_WEATHER_PLUGIN_DIR . 'includes/Autoloader.php';
\Zul\Weather\Autoloader::register();

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['Zul\\Weather\\Activation', 'activate']);
register_deactivation_hook(__FILE__, ['Zul\\Weather\\Activation', 'deactivate']);

// Initialize plugin on plugins_loaded
add_action('plugins_loaded', function () {
    $plugin = new \Zul\Weather\Plugin();
    $plugin->init();
});
