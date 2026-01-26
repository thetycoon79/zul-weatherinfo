<?php
/**
 * Settings page for Weather Info plugin
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Admin;

use Zul\Weather\Capabilities;

class SettingsPage
{
    private const OPTION_GROUP = 'zul_weather_settings';
    private const PAGE_SLUG = 'zul-weather-settings';

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings(): void
    {
        // Register settings
        register_setting(
            self::OPTION_GROUP,
            'zul_weather_api_key',
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            ]
        );

        register_setting(
            self::OPTION_GROUP,
            'zul_weather_cache_minutes',
            [
                'type' => 'integer',
                'sanitize_callback' => [$this, 'sanitizeCacheMinutes'],
                'default' => 10,
            ]
        );

        // Add settings section
        add_settings_section(
            'zul_weather_general',
            __('API Configuration', 'zul-weather'),
            [$this, 'renderSectionDescription'],
            self::PAGE_SLUG
        );

        // Add fields
        add_settings_field(
            'zul_weather_api_key',
            __('OpenWeatherMap API Key', 'zul-weather'),
            [$this, 'renderApiKeyField'],
            self::PAGE_SLUG,
            'zul_weather_general'
        );

        add_settings_field(
            'zul_weather_cache_minutes',
            __('Cache Duration (minutes)', 'zul-weather'),
            [$this, 'renderCacheField'],
            self::PAGE_SLUG,
            'zul_weather_general'
        );
    }

    public function sanitizeCacheMinutes($value): int
    {
        $value = absint($value);
        return $value > 0 ? $value : 10; // Fallback to 10 if invalid
    }

    public function renderSectionDescription(): void
    {
        ?>
        <p>
            <?php
            printf(
                /* translators: %s: URL to OpenWeatherMap API */
                esc_html__('Configure your OpenWeatherMap API settings. You can get an API key from %s.', 'zul-weather'),
                '<a href="https://openweathermap.org/api" target="_blank" rel="noopener">OpenWeatherMap</a>'
            );
            ?>
        </p>
        <p>
            <em><?php esc_html_e('Note: If no API key is provided, the plugin will use mock data for demonstration purposes.', 'zul-weather'); ?></em>
        </p>
        <?php
    }

    public function renderApiKeyField(): void
    {
        $value = get_option('zul_weather_api_key', '');
        ?>
        <input type="text"
               id="zul_weather_api_key"
               name="zul_weather_api_key"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="<?php esc_attr_e('Enter your API key', 'zul-weather'); ?>">
        <p class="description">
            <?php esc_html_e('Your OpenWeatherMap One Call API 3.0 key.', 'zul-weather'); ?>
        </p>
        <?php
    }

    public function renderCacheField(): void
    {
        $value = get_option('zul_weather_cache_minutes', 10);
        ?>
        <input type="number"
               id="zul_weather_cache_minutes"
               name="zul_weather_cache_minutes"
               value="<?php echo esc_attr($value); ?>"
               class="small-text"
               min="1"
               max="1440">
        <p class="description">
            <?php esc_html_e('How long to cache weather data (1-1440 minutes). Default: 10 minutes.', 'zul-weather'); ?>
        </p>
        <?php
    }

    public function renderPage(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to access this page.', 'zul-weather'));
        }

        include ZUL_WEATHER_PLUGIN_DIR . 'includes/Admin/Views/settings.php';
    }
}
