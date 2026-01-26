<?php
/**
 * Weather data cache using WordPress transients
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Cache;

class WeatherCache
{
    private const CACHE_KEY_PREFIX = 'zul_weather_';

    /**
     * Get cached weather data for a location
     *
     * @param int $locationId Location ID
     * @return array|null Cached data or null if not found
     */
    public function get(int $locationId): ?array
    {
        $key = $this->getCacheKey($locationId);
        $cached = get_transient($key);
        return $cached !== false ? $cached : null;
    }

    /**
     * Cache weather data for a location
     *
     * @param int $locationId Location ID
     * @param array $data Weather data to cache
     * @return bool Success
     */
    public function set(int $locationId, array $data): bool
    {
        $key = $this->getCacheKey($locationId);
        $ttl = $this->getTtl();
        return set_transient($key, $data, $ttl);
    }

    /**
     * Delete cached weather data for a location
     *
     * @param int $locationId Location ID
     * @return bool Success
     */
    public function delete(int $locationId): bool
    {
        return delete_transient($this->getCacheKey($locationId));
    }

    /**
     * Clear all weather cache entries
     *
     * @return void
     */
    public function flush(): void
    {
        global $wpdb;

        // Delete all transients with our prefix
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . self::CACHE_KEY_PREFIX . '%',
                '_transient_timeout_' . self::CACHE_KEY_PREFIX . '%'
            )
        );
    }

    /**
     * Generate cache key for a location
     *
     * @param int $locationId Location ID
     * @return string Cache key
     */
    private function getCacheKey(int $locationId): string
    {
        return self::CACHE_KEY_PREFIX . $locationId;
    }

    /**
     * Get cache TTL from settings
     *
     * @return int TTL in seconds
     */
    private function getTtl(): int
    {
        $minutes = (int) get_option('zul_weather_cache_minutes', 10);
        return max(1, $minutes) * 60; // Convert to seconds, minimum 1 minute
    }
}
