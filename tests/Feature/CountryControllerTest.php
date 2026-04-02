<?php

use App\Http\Controllers\Api\CountryController;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

test('country show caches payload arrays instead of response objects', function () {
    $country = new Country([
        'id' => 101,
        'name' => 'India',
        'code' => 'IN',
        'iso3' => 'IND',
        'phonecode' => '91',
        'flag' => '.in',
    ]);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(function (string $key, $ttl, callable $callback) {
            $payload = $callback();

            expect($key)->toStartWith('api:countries.show.v3:');
            expect($payload)->toBeArray();
            expect($payload['success'])->toBeTrue();
            expect($payload['data'])->toBeArray();

            return $payload;
        });

    $response = app(CountryController::class)->show(
        $country,
        Request::create('/api/v1/countries/101', 'GET')
    );

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->headers->get('Cache-Control'))->toContain('max-age=60');
    expect($response->headers->get('Cache-Control'))->toContain('private');
    expect($response->headers->get('Cache-Control'))->toContain('stale-while-revalidate=30');
    expect($response->headers->get('Vary'))->toBe('Accept, Authorization');
    expect($response->headers->get('ETag'))->not->toBeNull();
    expect($response->getData(true))->toMatchArray([
        'success' => true,
        'data' => [
            'id' => 101,
            'name' => 'India',
            'code' => 'IN',
            'iso3' => 'IND',
            'phonecode' => '91',
            'flag' => '.in',
        ],
    ]);
});