<?php
/**
 * Weather Cache Tests
 *
 * @package Zul\Weather\Tests\Unit\Cache
 */

namespace Zul\Weather\Tests\Unit\Cache;

use Zul\Weather\Tests\TestCase;
use Zul\Weather\Cache\WeatherCache;
use Brain\Monkey\Functions;

class WeatherCacheTest extends TestCase
{
    private WeatherCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new WeatherCache();
    }

    public function testGetReturnsCachedDataWhenExists(): void
    {
        $expectedData = ['temperature' => 20, 'humidity' => 65];

        Functions\when('get_transient')
            ->justReturn($expectedData);

        $result = $this->cache->get(1);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetReturnsNullWhenCacheMiss(): void
    {
        Functions\when('get_transient')
            ->justReturn(false);

        $result = $this->cache->get(1);

        $this->assertNull($result);
    }

    public function testSetStoresDataWithCorrectTtl(): void
    {
        $data = ['temperature' => 20, 'humidity' => 65];

        // Mock get_option to return 10 minutes
        Functions\when('get_option')
            ->justReturn(10);

        Functions\expect('set_transient')
            ->once()
            ->with('zul_weather_1', $data, 600) // 10 minutes = 600 seconds
            ->andReturn(true);

        $result = $this->cache->set(1, $data);

        $this->assertTrue($result);
    }

    public function testSetUsesDefaultTtlWhenOptionInvalid(): void
    {
        $data = ['temperature' => 20];

        // Mock get_option to return invalid value
        Functions\when('get_option')
            ->justReturn(0);

        Functions\expect('set_transient')
            ->once()
            ->with('zul_weather_1', $data, 60) // Minimum 1 minute = 60 seconds
            ->andReturn(true);

        $result = $this->cache->set(1, $data);

        $this->assertTrue($result);
    }

    public function testDeleteRemovesTransient(): void
    {
        Functions\expect('delete_transient')
            ->once()
            ->with('zul_weather_1')
            ->andReturn(true);

        $result = $this->cache->delete(1);

        $this->assertTrue($result);
    }
}
