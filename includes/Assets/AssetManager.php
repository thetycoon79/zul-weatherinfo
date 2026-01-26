<?php
/**
 * Asset manager - handles script and style enqueueing
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Assets;

class AssetManager
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'registerFrontendAssets']);
    }

    public function enqueueAdminAssets(string $hook): void
    {
        // Only load on our admin pages
        if (strpos($hook, 'zul-weather') === false && strpos($hook, 'zul_weather') === false) {
            return;
        }

        // Admin styles
        wp_enqueue_style(
            'zul-weather-admin',
            ZUL_WEATHER_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ZUL_WEATHER_VERSION
        );

        // Admin scripts (if needed in future)
        wp_enqueue_script(
            'zul-weather-admin',
            ZUL_WEATHER_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            ZUL_WEATHER_VERSION,
            true
        );
    }

    public function registerFrontendAssets(): void
    {
        // Plugin frontend styles
        wp_register_style(
            'zul-weather-frontend',
            ZUL_WEATHER_PLUGIN_URL . 'assets/css/weather.css',
            [],
            ZUL_WEATHER_VERSION
        );

        // Plugin frontend scripts
        wp_register_script(
            'zul-weather-frontend',
            ZUL_WEATHER_PLUGIN_URL . 'assets/js/weather.js',
            [],
            ZUL_WEATHER_VERSION,
            true
        );
    }
}
