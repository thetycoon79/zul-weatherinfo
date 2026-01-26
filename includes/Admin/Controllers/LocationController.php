<?php
/**
 * Location admin controller
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Admin\Controllers;

use Zul\Weather\Capabilities;
use Zul\Weather\Domain\Entities\Location;
use Zul\Weather\Domain\ValueObjects\Status;
use Zul\Weather\Interfaces\LocationRepositoryInterface;
use Zul\Weather\Services\WeatherService;
use Zul\Weather\Support\Nonce;
use Zul\Weather\Support\Validator;

class LocationController
{
    private LocationRepositoryInterface $repository;
    private WeatherService $weatherService;
    private Nonce $nonce;

    public function __construct(
        LocationRepositoryInterface $repository,
        WeatherService $weatherService
    ) {
        $this->repository = $repository;
        $this->weatherService = $weatherService;
        $this->nonce = new Nonce('zul_weather_action');
    }

    public function listAction(): void
    {
        if (!Capabilities::userCanViewAdmin()) {
            wp_die(__('You do not have permission to view this page.', 'zul-weather'));
        }

        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $this->deleteAction();
            return;
        }

        // Handle edit action
        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            $this->editAction();
            return;
        }

        // Get filters
        $filters = $this->getFiltersFromRequest();
        $page = max(1, absint($_GET['paged'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $locations = $this->repository->list($filters, $perPage, $offset);
        $total = $this->repository->count($filters);
        $totalPages = ceil($total / $perPage);

        include ZUL_WEATHER_PLUGIN_DIR . 'includes/Admin/Views/location-list.php';
    }

    public function createAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to create locations.', 'zul-weather'));
        }

        $location = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->handleSave();
        }

        include ZUL_WEATHER_PLUGIN_DIR . 'includes/Admin/Views/location-edit.php';
    }

    public function editAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to edit locations.', 'zul-weather'));
        }

        $id = absint($_GET['id'] ?? 0);

        if (!$id) {
            wp_redirect(admin_url('admin.php?page=zul-weather'));
            exit;
        }

        $location = $this->repository->findById($id);

        if (!$location) {
            wp_die(__('Location not found.', 'zul-weather'));
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->handleSave($id);
            if (empty($errors)) {
                $location = $this->repository->findById($id);
            }
        }

        include ZUL_WEATHER_PLUGIN_DIR . 'includes/Admin/Views/location-edit.php';
    }

    public function deleteAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to delete locations.', 'zul-weather'));
        }

        $id = absint($_GET['id'] ?? 0);

        if (!$id) {
            wp_redirect(admin_url('admin.php?page=zul-weather'));
            exit;
        }

        if (!$this->nonce->verify($_GET['_wpnonce'] ?? '', '_wpnonce')) {
            wp_die(__('Security check failed.', 'zul-weather'));
        }

        try {
            // Clear cache before deleting
            $this->weatherService->clearCache($id);
            $this->repository->delete($id);

            $this->addNotice(__('Location deleted successfully.', 'zul-weather'), 'success');
        } catch (\Exception $e) {
            $this->addNotice($e->getMessage(), 'error');
        }

        wp_redirect(admin_url('admin.php?page=zul-weather'));
        exit;
    }

    private function handleSave(?int $id = null): array
    {
        $errors = [];

        if (!$this->nonce->verify($_POST['_wpnonce'] ?? '', '_wpnonce')) {
            return [__('Security check failed.', 'zul-weather')];
        }

        $data = $this->sanitizeInput($_POST);

        // Validate input
        $validator = new Validator();
        $validator
            ->required('location', $data['location'])
            ->maxLength('location', $data['location'], 255)
            ->required('latitude', $data['latitude'])
            ->latitude('latitude', $data['latitude'])
            ->required('longitude', $data['longitude'])
            ->longitude('longitude', $data['longitude']);

        if ($validator->hasErrors()) {
            return array_values($validator->getErrors());
        }

        try {
            if ($id) {
                // Update existing location
                $existing = $this->repository->findById($id);
                if (!$existing) {
                    return [__('Location not found.', 'zul-weather')];
                }

                $updated = $existing
                    ->withLocation($data['location'])
                    ->withDescription($data['description'])
                    ->withCoordinates((float) $data['latitude'], (float) $data['longitude'])
                    ->withStatus(Status::fromString($data['status']));

                $this->repository->update($updated);

                // Clear cache on update
                $this->weatherService->clearCache($id);

                $this->addNotice(__('Location updated successfully.', 'zul-weather'), 'success');
            } else {
                // Create new location
                $location = new Location(
                    location: $data['location'],
                    latitude: (float) $data['latitude'],
                    longitude: (float) $data['longitude'],
                    createdBy: get_current_user_id(),
                    description: $data['description'],
                    status: Status::fromString($data['status'])
                );

                $newId = $this->repository->insert($location);

                wp_redirect(admin_url('admin.php?page=zul-weather&action=edit&id=' . $newId));
                exit;
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'location' => Validator::sanitizeText($input['location'] ?? ''),
            'description' => Validator::sanitizeTextarea($input['description'] ?? ''),
            'latitude' => Validator::sanitizeFloat($input['latitude'] ?? 0),
            'longitude' => Validator::sanitizeFloat($input['longitude'] ?? 0),
            'status' => Validator::sanitizeText($input['status'] ?? 'active'),
        ];
    }

    private function getFiltersFromRequest(): array
    {
        $filters = [];

        if (!empty($_GET['status'])) {
            $filters['status'] = sanitize_text_field($_GET['status']);
        }

        if (!empty($_GET['s'])) {
            $filters['search'] = sanitize_text_field($_GET['s']);
        }

        if (!empty($_GET['orderby'])) {
            $filters['orderby'] = sanitize_text_field($_GET['orderby']);
        }

        if (!empty($_GET['order'])) {
            $filters['order'] = sanitize_text_field($_GET['order']);
        }

        return $filters;
    }

    private function addNotice(string $message, string $type = 'success'): void
    {
        add_settings_error(
            'zul_weather_notices',
            'zul_weather_notice',
            $message,
            $type
        );
    }

    public function getDeleteUrl(int $id): string
    {
        return wp_nonce_url(
            admin_url('admin.php?page=zul-weather&action=delete&id=' . $id),
            'zul_weather_action',
            '_wpnonce'
        );
    }

    public function getEditUrl(int $id): string
    {
        return admin_url('admin.php?page=zul-weather&action=edit&id=' . $id);
    }
}
