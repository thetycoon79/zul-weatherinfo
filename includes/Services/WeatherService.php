<?php
/**
 * Weather service - orchestrates repository, cache, and API client
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Services;

use Zul\Weather\Api\OpenWeatherClient;
use Zul\Weather\Cache\WeatherCache;
use Zul\Weather\Domain\Entities\Location;
use Zul\Weather\Interfaces\LocationRepositoryInterface;
use Zul\Weather\Interfaces\WeatherClientInterface;

class WeatherService
{
    private LocationRepositoryInterface $repository;
    private WeatherCache $cache;
    private ?WeatherClientInterface $client;
    private string $mockFile;

    public function __construct(
        LocationRepositoryInterface $repository,
        WeatherCache $cache,
        ?WeatherClientInterface $client = null
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->client = $client;
        $this->mockFile = ZUL_WEATHER_PLUGIN_DIR . 'mock_response.json';
    }

    /**
     * Get weather data for a location
     *
     * @param int $locationId Location ID
     * @return array|null Weather data or null if unavailable
     */
    public function getWeatherForLocation(int $locationId): ?array
    {
        $location = $this->repository->findActiveById($locationId);

        if (!$location) {
            return null;
        }

        // No API key = use mock response
        if (!$this->hasApiKey()) {
            return $this->getMockResponse($location);
        }

        // Check cache first
        $cached = $this->cache->get($locationId);
        if ($cached !== null) {
            $cached['from_cache'] = true;
            return $cached;
        }

        // Fetch from API
        try {
            $client = $this->getClient();
            $data = $client->fetch(
                $location->getLatitude(),
                $location->getLongitude()
            );

            $result = $this->normalizeResponse($location, $data);
            $this->cache->set($locationId, $result);

            return $result;
        } catch (\Exception $e) {
            // Log error and return null
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Zul Weather API error: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get location by ID
     *
     * @param int $id Location ID
     * @return Location|null
     */
    public function getLocation(int $id): ?Location
    {
        return $this->repository->findById($id);
    }

    /**
     * Get active location by ID
     *
     * @param int $id Location ID
     * @return Location|null
     */
    public function getActiveLocation(int $id): ?Location
    {
        return $this->repository->findActiveById($id);
    }

    /**
     * List all active locations
     *
     * @return Location[]
     */
    public function getActiveLocations(): array
    {
        return $this->repository->list(['status' => 'active'], -1);
    }

    /**
     * Check if API key is configured
     *
     * @return bool
     */
    public function hasApiKey(): bool
    {
        $apiKey = get_option('zul_weather_api_key', '');
        return !empty(trim($apiKey));
    }

    /**
     * Get the weather API client
     *
     * @return WeatherClientInterface
     */
    private function getClient(): WeatherClientInterface
    {
        if ($this->client === null) {
            $apiKey = get_option('zul_weather_api_key', '');
            $this->client = new OpenWeatherClient($apiKey);
        }
        return $this->client;
    }

    /**
     * Get mock weather response
     *
     * @param Location $location Location entity
     * @return array|null
     */
    private function getMockResponse(Location $location): ?array
    {
        if (!file_exists($this->mockFile)) {
            return null;
        }

        $json = file_get_contents($this->mockFile);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $this->normalizeResponse($location, $data, true);
    }

    /**
     * Normalize API response to standard format
     *
     * @param Location $location Location entity
     * @param array $data Raw API response
     * @param bool $isMock Whether this is mock data
     * @return array Normalized weather data
     */
    private function normalizeResponse(Location $location, array $data, bool $isMock = false): array
    {
        $current = $data['current'] ?? [];
        $weather = $current['weather'][0] ?? [];

        return [
            'location_id' => $location->getId(),
            'location' => $location->getLocation(),
            'description' => $location->getDescription(),
            'latitude' => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'temperature' => $current['temp'] ?? null,
            'feels_like' => $current['feels_like'] ?? null,
            'humidity' => $current['humidity'] ?? null,
            'pressure' => $current['pressure'] ?? null,
            'wind_speed' => $current['wind_speed'] ?? null,
            'weather_main' => $weather['main'] ?? null,
            'weather_description' => $weather['description'] ?? null,
            'weather_icon' => $weather['icon'] ?? null,
            'is_mock' => $isMock,
            'from_cache' => false,
            'timestamp' => time(),
        ];
    }

    /**
     * Clear weather cache for a location
     *
     * @param int $locationId Location ID
     * @return bool
     */
    public function clearCache(int $locationId): bool
    {
        return $this->cache->delete($locationId);
    }

    /**
     * Clear all weather caches
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        $this->cache->flush();
    }
}
