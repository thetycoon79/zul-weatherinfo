<?php
/**
 * Main plugin orchestrator
 *
 * @package Zul\Weather
 */

namespace Zul\Weather;

use Zul\Weather\Admin\Menu;
use Zul\Weather\Admin\SettingsPage;
use Zul\Weather\Admin\Controllers\LocationController;
use Zul\Weather\Assets\AssetManager;
use Zul\Weather\Cache\WeatherCache;
use Zul\Weather\Repositories\LocationRepository;
use Zul\Weather\Services\WeatherService;
use Zul\Weather\Shortcode\WeatherShortcode;
use Zul\Weather\Support\Db;

class Plugin
{
    private static ?Plugin $instance = null;

    // Core dependencies
    private Db $db;
    private LocationRepository $locationRepository;
    private WeatherCache $weatherCache;
    private WeatherService $weatherService;

    // Components
    private AssetManager $assetManager;
    private SettingsPage $settingsPage;
    private ?Menu $adminMenu = null;
    private ?LocationController $locationController = null;
    private ?WeatherShortcode $shortcode = null;

    public function __construct()
    {
        $this->initializeDependencies();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        // Check for database upgrades
        Activation::maybeUpgrade();

        // Initialize components
        $this->initializeAssets();
        $this->initializeSettings();
        $this->initializeAdmin();
        $this->initializeFrontend();

        // Register cache clear handler
        $this->registerCacheClearHandler();

        // Allow extensions
        do_action('zul_weather_loaded', $this);
    }

    private function initializeDependencies(): void
    {
        // Database layer
        $this->db = new Db();

        // Repository
        $this->locationRepository = new LocationRepository($this->db);

        // Cache
        $this->weatherCache = new WeatherCache();

        // Service
        $this->weatherService = new WeatherService(
            $this->locationRepository,
            $this->weatherCache
        );
    }

    private function initializeAssets(): void
    {
        $this->assetManager = new AssetManager();
        $this->assetManager->register();
    }

    private function initializeSettings(): void
    {
        $this->settingsPage = new SettingsPage();
        $this->settingsPage->register();
    }

    private function initializeAdmin(): void
    {
        if (!is_admin()) {
            return;
        }

        // Location controller
        $this->locationController = new LocationController(
            $this->locationRepository,
            $this->weatherService
        );

        // Admin menu
        $this->adminMenu = new Menu($this->locationController, $this->settingsPage);
        $this->adminMenu->register();
    }

    private function initializeFrontend(): void
    {
        // Shortcode
        $this->shortcode = new WeatherShortcode($this->weatherService);
        add_action('init', [$this->shortcode, 'register']);
    }

    private function registerCacheClearHandler(): void
    {
        add_action('admin_post_zul_weather_clear_cache', function () {
            if (!Capabilities::userCanManage()) {
                wp_die(__('You do not have permission to perform this action.', 'zul-weather'));
            }

            if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zul_weather_clear_cache')) {
                wp_die(__('Security check failed.', 'zul-weather'));
            }

            $this->weatherService->clearAllCaches();

            add_settings_error(
                'zul_weather_notices',
                'zul_weather_notice',
                __('Weather cache cleared successfully.', 'zul-weather'),
                'success'
            );

            wp_redirect(admin_url('admin.php?page=zul-weather-settings'));
            exit;
        });
    }

    // Getters for external access
    public function getWeatherService(): WeatherService
    {
        return $this->weatherService;
    }

    public function getLocationRepository(): LocationRepository
    {
        return $this->locationRepository;
    }

    public function getWeatherCache(): WeatherCache
    {
        return $this->weatherCache;
    }
}
