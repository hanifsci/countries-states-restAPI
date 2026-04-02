<?php

use App\Http\Controllers\Api\CityController;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

test('city show caches payload arrays instead of response objects', function () {
    $state = new State([
        'id' => 4006,
        'name' => 'Meghalaya',
        'country_id' => 101,
    ]);

    $city = new class extends City {
        public function load($relations)
        {
            return $this;
        }
    };
    $city->forceFill([
        'id' => 1,
        'name' => 'Shillong',
        'state_id' => 4006,
    ]);

    $city->setRelation('state', $state);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(function (string $key, $ttl, callable $callback) {
            $payload = $callback();

            expect($key)->toStartWith('api:cities.show.v3:');
            expect($payload)->toBeArray();
            expect($payload['success'])->toBeTrue();
            expect($payload['data'])->toBeArray();

            return $payload;
        });

    $response = app(CityController::class)->show(
        $city,
        Request::create('/api/v1/cities/1', 'GET')
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
            'id' => 1,
            'name' => 'Shillong',
            'state_id' => 4006,
            'state' => [
                'id' => 4006,
                'name' => 'Meghalaya',
            ],
        ],
    ]);
});