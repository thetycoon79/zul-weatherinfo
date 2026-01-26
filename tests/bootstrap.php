<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Zul\Weather\Tests
 */

// Composer autoloader
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Define WordPress constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('ZUL_WEATHER_VERSION')) {
    define('ZUL_WEATHER_VERSION', '1.0.0');
}

if (!defined('ZUL_WEATHER_PLUGIN_DIR')) {
    define('ZUL_WEATHER_PLUGIN_DIR', dirname(__DIR__) . '/');
}

if (!defined('ZUL_WEATHER_PLUGIN_URL')) {
    define('ZUL_WEATHER_PLUGIN_URL', 'http://example.com/wp-content/plugins/zul-weatherinfo/');
}

// Initialize Brain\Monkey
\Brain\Monkey\setUp();

// Load plugin autoloader
require_once ZUL_WEATHER_PLUGIN_DIR . 'includes/Autoloader.php';
\Zul\Weather\Autoloader::register();

// Register teardown
register_shutdown_function(function () {
    \Brain\Monkey\tearDown();
});
