<?php
/**
 * OpenWeather Client Tests
 *
 * @package Zul\Weather\Tests\Unit\Api
 */

namespace Zul\Weather\Tests\Unit\Api;

use Zul\Weather\Tests\TestCase;
use Zul\Weather\Api\OpenWeatherClient;
use Brain\Monkey\Functions;

class OpenWeatherClientTest extends TestCase
{
    private OpenWeatherClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new OpenWeatherClient('test-api-key');
    }

    public function testFetchReturnsWeatherData(): void
    {
        // Free API 2.5 response format
        $responseBody = json_encode([
            'main' => [
                'temp' => 22.5,
                'feels_like' => 21.0,
                'humidity' => 65,
                'pressure' => 1015
            ],
            'wind' => [
                'speed' => 3.5
            ],
            'weather' => [
                ['main' => 'Clouds', 'description' => 'scattered clouds', 'icon' => '03d']
            ]
        ]);

        Functions\when('add_query_arg')
            ->justReturn('https://api.openweathermap.org/data/2.5/weather?lat=51.5&lon=-0.1&appid=test-api-key');

        Functions\when('wp_remote_get')
            ->justReturn(['body' => $responseBody]);

        Functions\when('is_wp_error')
            ->justReturn(false);

        Functions\when('wp_remote_retrieve_response_code')
            ->justReturn(200);

        Functions\when('wp_remote_retrieve_body')
            ->justReturn($responseBody);

        $result = $this->client->fetch(51.5074, -0.1278);

        // Result is normalized to One Call format
        $this->assertIsArray($result);
        $this->assertEquals(22.5, $result['current']['temp']);
        $this->assertEquals(65, $result['current']['humidity']);
        $this->assertEquals(3.5, $result['current']['wind_speed']);
    }

    public function testFetchThrowsExceptionOnWpError(): void
    {
        $wpError = \Mockery::mock('WP_Error');
        $wpError->shouldReceive('get_error_message')
            ->andReturn('Connection timeout');

        Functions\when('add_query_arg')
            ->justReturn('https://api.openweathermap.org/...');

        Functions\when('wp_remote_get')
            ->justReturn($wpError);

        Functions\when('is_wp_error')
            ->justReturn(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->client->fetch(51.5074, -0.1278);
    }

    public function testFetchThrowsExceptionOnNon200Response(): void
    {
        $responseBody = json_encode(['message' => 'Invalid API key']);

        Functions\when('add_query_arg')
            ->justReturn('https://api.openweathermap.org/...');

        Functions\when('wp_remote_get')
            ->justReturn(['body' => $responseBody]);

        Functions\when('is_wp_error')
            ->justReturn(false);

        Functions\when('wp_remote_retrieve_response_code')
            ->justReturn(401);

        Functions\when('wp_remote_retrieve_body')
            ->justReturn($responseBody);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->client->fetch(51.5074, -0.1278);
    }

    public function testFetchThrowsExceptionOnInvalidJson(): void
    {
        Functions\when('add_query_arg')
            ->justReturn('https://api.openweathermap.org/...');

        Functions\when('wp_remote_get')
            ->justReturn(['body' => 'invalid json']);

        Functions\when('is_wp_error')
            ->justReturn(false);

        Functions\when('wp_remote_retrieve_response_code')
            ->justReturn(200);

        Functions\when('wp_remote_retrieve_body')
            ->justReturn('invalid json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->fetch(51.5074, -0.1278);
    }
}
