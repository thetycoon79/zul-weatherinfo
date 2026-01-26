<?php
/**
 * Weather shortcode handler
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Shortcode;

use Zul\Weather\Services\WeatherService;

class WeatherShortcode
{
    private WeatherService $service;
    private bool $assetsEnqueued = false;

    public function __construct(WeatherService $service)
    {
        $this->service = $service;
    }

    public function register(): void
    {
        add_shortcode('zul_weather', [$this, 'render']);
    }

    public function render($atts): string
    {
        $atts = shortcode_atts([
            'id' => 0,
            'class' => '',
            'show_description' => 'no',
            'show_humidity' => 'yes',
            'show_wind' => 'no',
        ], $atts, 'zul_weather');

        $locationId = absint($atts['id']);

        if (!$locationId) {
            return $this->renderError(__('Location ID is required.', 'zul-weather'));
        }

        $weather = $this->service->getWeatherForLocation($locationId);

        if (!$weather) {
            $this->enqueueAssets();
            return $this->renderUnavailable();
        }

        $this->enqueueAssets();
        return $this->renderWeather($weather, $atts);
    }

    private function renderWeather(array $weather, array $atts): string
    {
        $classes = trim('zul-weather-widget ' . sanitize_html_class($atts['class']));

        ob_start();
        ?>
        <div class="<?php echo esc_attr($classes); ?>">
            <?php if ($weather['is_mock']): ?>
                <div class="zul-weather-notice">
                    <?php esc_html_e('Demo data', 'zul-weather'); ?>
                </div>
            <?php endif; ?>

            <div class="zul-weather-header">
                <div class="zul-weather-location">
                    <?php echo esc_html($weather['location']); ?>
                </div>
                <?php if ($atts['show_description'] === 'yes' && !empty($weather['description'])): ?>
                    <div class="zul-weather-location-desc">
                        <?php echo esc_html($weather['description']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="zul-weather-main">
                <?php if ($weather['weather_icon']): ?>
                    <div class="zul-weather-icon">
                        <img src="https://openweathermap.org/img/wn/<?php echo esc_attr($weather['weather_icon']); ?>@2x.png"
                             alt="<?php echo esc_attr($weather['weather_description'] ?? ''); ?>"
                             width="80" height="80">
                    </div>
                <?php endif; ?>

                <div class="zul-weather-temp">
                    <?php if ($weather['temperature'] !== null): ?>
                        <span class="temp-value"><?php echo esc_html(round($weather['temperature'])); ?></span>
                        <span class="temp-unit">&deg;C</span>
                    <?php else: ?>
                        <span class="temp-value">--</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($weather['weather_description']): ?>
                <div class="zul-weather-condition">
                    <?php echo esc_html(ucfirst($weather['weather_description'])); ?>
                </div>
            <?php endif; ?>

            <div class="zul-weather-details">
                <?php if ($atts['show_humidity'] === 'yes' && $weather['humidity'] !== null): ?>
                    <div class="zul-weather-detail">
                        <span class="detail-label"><?php esc_html_e('Humidity:', 'zul-weather'); ?></span>
                        <span class="detail-value"><?php echo esc_html($weather['humidity']); ?>%</span>
                    </div>
                <?php endif; ?>

                <?php if ($atts['show_wind'] === 'yes' && $weather['wind_speed'] !== null): ?>
                    <div class="zul-weather-detail">
                        <span class="detail-label"><?php esc_html_e('Wind:', 'zul-weather'); ?></span>
                        <span class="detail-value"><?php echo esc_html(round($weather['wind_speed'], 1)); ?> m/s</span>
                    </div>
                <?php endif; ?>

                <?php if ($weather['feels_like'] !== null): ?>
                    <div class="zul-weather-detail">
                        <span class="detail-label"><?php esc_html_e('Feels like:', 'zul-weather'); ?></span>
                        <span class="detail-value"><?php echo esc_html(round($weather['feels_like'])); ?>&deg;C</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function enqueueAssets(): void
    {
        if ($this->assetsEnqueued) {
            return;
        }

        wp_enqueue_style('zul-weather-frontend');
        wp_enqueue_script('zul-weather-frontend');

        $this->assetsEnqueued = true;
    }

    private function renderUnavailable(): string
    {
        ob_start();
        ?>
        <div class="zul-weather-widget zul-weather-unavailable">
            <div class="zul-weather-message">
                <?php esc_html_e('There appears to be some issue with our weather data, please check again later.', 'zul-weather'); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderError(string $message): string
    {
        // Only show errors to admins
        if (!current_user_can('manage_options')) {
            return '';
        }

        return sprintf(
            '<div class="zul-weather-error">%s</div>',
            esc_html($message)
        );
    }
}
