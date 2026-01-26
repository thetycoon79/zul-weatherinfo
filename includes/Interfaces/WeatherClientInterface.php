<?php
/**
 * Weather API client interface
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Interfaces;

interface WeatherClientInterface
{
    /**
     * Fetch weather data for given coordinates
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array Weather data
     * @throws \RuntimeException On API error
     */
    public function fetch(float $lat, float $lon): array;
}
