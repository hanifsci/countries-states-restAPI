<?php

use App\Http\Controllers\Api\StateController;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

test('state show caches payload arrays instead of response objects', function () {
    $country = new Country([
        'id' => 101,
        'name' => 'India',
        'code' => 'IN',
    ]);

    $state = new class extends State {
        public function load($relations)
        {
            return $this;
        }
    };
    $state->forceFill([
        'id' => 4006,
        'name' => 'Meghalaya',
        'country_id' => 101,
    ]);

    $state->setRelation('country', $country);
    $state->setRelation('cities', collect([
        new City([
            'id' => 1,
            'name' => 'Shillong',
            'state_id' => 4006,
        ]),
    ]));

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(function (string $key, $ttl, callable $callback) {
            $payload = $callback();

            expect($key)->toStartWith('api:states.show.v3:');
            expect($payload)->toBeArray();
            expect($payload['success'])->toBeTrue();
            expect($payload['data'])->toBeArray();
            expect($payload['data']['cities'])->toBeArray();

            return $payload;
        });

    $response = app(StateController::class)->show(
        $state,
        Request::create('/api/v1/states/4006?include=country,cities', 'GET')
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
            'id' => 4006,
            'name' => 'Meghalaya',
            'country_id' => 101,
            'cities_count' => 1,
            'country' => [
                'id' => 101,
                'name' => 'India',
                'code' => 'IN',
            ],
            'cities' => [
                [
                    'id' => 1,
                    'name' => 'Shillong',
                    'state_id' => 4006,
                ],
            ],
        ],
    ]);
});