<?php
/**
 * OpenWeatherMap API client
 *
 * Uses Current Weather API 2.5 (free tier) by default.
 * Can be configured to use One Call API 3.0 (requires paid subscription).
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Api;

use Zul\Weather\Interfaces\WeatherClientInterface;

class OpenWeatherClient implements WeatherClientInterface
{
    private const API_URL_FREE = 'https://api.openweathermap.org/data/2.5/weather';
    private const API_URL_ONECALL = 'https://api.openweathermap.org/data/3.0/onecall';

    private string $apiKey;
    private bool $useOneCall;

    public function __construct(string $apiKey, bool $useOneCall = false)
    {
        $this->apiKey = $apiKey;
        $this->useOneCall = $useOneCall;
    }

    /**
     * Fetch weather data for given coordinates
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array Weather data normalized to One Call format
     * @throws \RuntimeException On API error
     */
    public function fetch(float $lat, float $lon): array
    {
        if ($this->useOneCall) {
            return $this->fetchOneCall($lat, $lon);
        }

        return $this->fetchFreeApi($lat, $lon);
    }

    /**
     * Fetch from free Current Weather API 2.5
     */
    private function fetchFreeApi(float $lat, float $lon): array
    {
        $url = add_query_arg([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'units' => 'metric',
        ], self::API_URL_FREE);

        $data = $this->makeRequest($url);

        // Normalize to One Call API format for compatibility
        return [
            'current' => [
                'temp' => $data['main']['temp'] ?? null,
                'feels_like' => $data['main']['feels_like'] ?? null,
                'humidity' => $data['main']['humidity'] ?? null,
                'pressure' => $data['main']['pressure'] ?? null,
                'wind_speed' => $data['wind']['speed'] ?? null,
                'weather' => $data['weather'] ?? [],
            ],
        ];
    }

    /**
     * Fetch from One Call API 3.0 (requires paid subscription)
     */
    private function fetchOneCall(float $lat, float $lon): array
    {
        $url = add_query_arg([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'units' => 'metric',
            'exclude' => 'minutely,hourly,daily,alerts',
        ], self::API_URL_ONECALL);

        return $this->makeRequest($url);
    }

    /**
     * Make HTTP request to OpenWeatherMap API
     */
    private function makeRequest(string $url): array
    {
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException(
                'Weather API request failed: ' . $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error = json_decode($body, true);
            $message = $error['message'] ?? "HTTP {$code}";
            throw new \RuntimeException('Weather API error: ' . $message);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response from Weather API');
        }

        return $data;
    }
}
