<?php
/**
 * Settings page view
 *
 * @package Zul\Weather
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Weather Info Settings', 'zul-weather'); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('zul_weather_settings');
        do_settings_sections('zul-weather-settings');
        submit_button();
        ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Cache Management', 'zul-weather'); ?></h2>
    <p><?php esc_html_e('Clear all cached weather data. This will force fresh API requests for all locations.', 'zul-weather'); ?></p>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('zul_weather_clear_cache', '_wpnonce'); ?>
        <input type="hidden" name="action" value="zul_weather_clear_cache">
        <?php submit_button(__('Clear All Weather Cache', 'zul-weather'), 'secondary', 'clear_cache', false); ?>
    </form>
</div>
