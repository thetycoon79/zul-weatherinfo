<?php
/**
 * Admin menu registration
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Admin;

use Zul\Weather\Capabilities;
use Zul\Weather\Admin\Controllers\LocationController;

class Menu
{
    private LocationController $locationController;
    private SettingsPage $settingsPage;

    public function __construct(LocationController $locationController, SettingsPage $settingsPage)
    {
        $this->locationController = $locationController;
        $this->settingsPage = $settingsPage;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
    }

    public function addMenuPages(): void
    {
        // Main menu
        add_menu_page(
            __('Weather Info', 'zul-weather'),
            __('Weather Info', 'zul-weather'),
            Capabilities::VIEW_ADMIN,
            'zul-weather',
            [$this->locationController, 'listAction'],
            'dashicons-cloud',
            30
        );

        // All Locations submenu (same as main)
        add_submenu_page(
            'zul-weather',
            __('All Locations', 'zul-weather'),
            __('All Locations', 'zul-weather'),
            Capabilities::VIEW_ADMIN,
            'zul-weather',
            [$this->locationController, 'listAction']
        );

        // Add New Location submenu
        add_submenu_page(
            'zul-weather',
            __('Add New Location', 'zul-weather'),
            __('Add New', 'zul-weather'),
            Capabilities::MANAGE,
            'zul-weather-new',
            [$this->locationController, 'createAction']
        );

        // Settings submenu
        add_submenu_page(
            'zul-weather',
            __('Weather Settings', 'zul-weather'),
            __('Settings', 'zul-weather'),
            Capabilities::MANAGE,
            'zul-weather-settings',
            [$this->settingsPage, 'renderPage']
        );
    }

    public function getCurrentScreen(): string
    {
        $page = $_GET['page'] ?? '';

        if (strpos($page, 'zul-weather') === false) {
            return '';
        }

        $action = $_GET['action'] ?? 'list';

        return match ($page) {
            'zul-weather' => match ($action) {
                'edit' => 'edit',
                'delete' => 'delete',
                default => 'list',
            },
            'zul-weather-new' => 'create',
            'zul-weather-settings' => 'settings',
            default => '',
        };
    }
}
