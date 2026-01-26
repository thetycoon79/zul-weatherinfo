<?php
/**
 * Weather Service Tests
 *
 * @package Zul\Weather\Tests\Unit\Services
 */

namespace Zul\Weather\Tests\Unit\Services;

use Zul\Weather\Tests\TestCase;
use Zul\Weather\Services\WeatherService;
use Zul\Weather\Cache\WeatherCache;
use Zul\Weather\Domain\Entities\Location;
use Zul\Weather\Domain\ValueObjects\Status;
use Zul\Weather\Interfaces\LocationRepositoryInterface;
use Zul\Weather\Interfaces\WeatherClientInterface;
use Mockery;
use Brain\Monkey\Functions;

class WeatherServiceTest extends TestCase
{
    private LocationRepositoryInterface $repository;
    private WeatherCache $cache;
    private WeatherClientInterface $client;
    private WeatherService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(LocationRepositoryInterface::class);
        $this->cache = Mockery::mock(WeatherCache::class);
        $this->client = Mockery::mock(WeatherClientInterface::class);

        $this->service = new WeatherService(
            $this->repository,
            $this->cache,
            $this->client
        );
    }

    public function testGetWeatherForLocationReturnsNullWhenLocationNotFound(): void
    {
        $this->repository
            ->shouldReceive('findActiveById')
            ->with(1)
            ->once()
            ->andReturn(null);

        $result = $this->service->getWeatherForLocation(1);

        $this->assertNull($result);
    }

    public function testGetWeatherForLocationReturnsCachedData(): void
    {
        Functions\when('get_option')->justReturn('test-api-key');

        $location = $this->createTestLocation();
        $cachedData = ['location' => 'London', 'temperature' => 20];

        $this->repository
            ->shouldReceive('findActiveById')
            ->with(1)
            ->once()
            ->andReturn($location);

        $this->cache
            ->shouldReceive('get')
            ->with(1)
            ->once()
            ->andReturn($cachedData);

        $result = $this->service->getWeatherForLocation(1);

        $this->assertIsArray($result);
        $this->assertTrue($result['from_cache']);
    }

    public function testGetWeatherForLocationFetchesFromApiWhenCacheMiss(): void
    {
        Functions\when('get_option')->justReturn('test-api-key');

        $location = $this->createTestLocation();
        $apiResponse = [
            'current' => [
                'temp' => 22.5,
                'humidity' => 65,
                'feels_like' => 21.0,
                'pressure' => 1015,
                'wind_speed' => 3.5,
                'weather' => [
                    ['main' => 'Clouds', 'description' => 'scattered clouds', 'icon' => '03d']
                ]
            ]
        ];

        $this->repository
            ->shouldReceive('findActiveById')
            ->with(1)
            ->once()
            ->andReturn($location);

        $this->cache
            ->shouldReceive('get')
            ->with(1)
            ->once()
            ->andReturn(null);

        $this->client
            ->shouldReceive('fetch')
            ->with(51.5074, -0.1278)
            ->once()
            ->andReturn($apiResponse);

        $this->cache
            ->shouldReceive('set')
            ->with(1, Mockery::type('array'))
            ->once()
            ->andReturn(true);

        $result = $this->service->getWeatherForLocation(1);

        $this->assertIsArray($result);
        $this->assertEquals('London', $result['location']);
        $this->assertEquals(22.5, $result['temperature']);
        $this->assertEquals(65, $result['humidity']);
        $this->assertFalse($result['is_mock']);
        $this->assertFalse($result['from_cache']);
    }

    public function testGetWeatherForLocationReturnsNullOnApiError(): void
    {
        Functions\when('get_option')->justReturn('test-api-key');

        $location = $this->createTestLocation();

        $this->repository
            ->shouldReceive('findActiveById')
            ->with(1)
            ->once()
            ->andReturn($location);

        $this->cache
            ->shouldReceive('get')
            ->with(1)
            ->once()
            ->andReturn(null);

        $this->client
            ->shouldReceive('fetch')
            ->with(51.5074, -0.1278)
            ->once()
            ->andThrow(new \RuntimeException('API Error'));

        $result = $this->service->getWeatherForLocation(1);

        $this->assertNull($result);
    }

    public function testHasApiKeyReturnsTrueWhenKeySet(): void
    {
        Functions\when('get_option')->justReturn('test-api-key');

        $this->assertTrue($this->service->hasApiKey());
    }

    public function testHasApiKeyReturnsFalseWhenKeyEmpty(): void
    {
        Functions\when('get_option')->justReturn('');

        $this->assertFalse($this->service->hasApiKey());
    }

    public function testClearCacheDeletesTransient(): void
    {
        $this->cache
            ->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturn(true);

        $result = $this->service->clearCache(1);

        $this->assertTrue($result);
    }

    private function createTestLocation(): Location
    {
        return new Location(
            location: 'London',
            latitude: 51.5074,
            longitude: -0.1278,
            createdBy: 1,
            description: 'United Kingdom',
            status: Status::ACTIVE,
            id: 1
        );
    }
}
