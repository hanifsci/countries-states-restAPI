<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Support\ApiResponseCache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('cities.index.v3', $request, function () use ($request) {
            $query = City::query()->orderBy('name');

            // Filter by state_id
            if ($stateId = $request->query('state_id')) {
                $query->where('state_id', $stateId);
            }

            // Filter by country_id (through state)
            if ($countryId = $request->query('country_id')) {
                $query->whereHas('state', fn($q) => $q->where('country_id', $countryId));
            }

            // Search by city name
            if ($search = $request->query('search')) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            // If state_id is provided → return ALL cities (no pagination) for that state
            if ($request->has('state_id') && !$request->has('search') && !$request->has('page')) {
                $cities = $query->limit(5000)->get();    // Get all (safe because one state won't have 100k cities)

                return [
                    'success' => true,
                    'data'    => CityResource::collection($cities)->resolve($request),
                    'meta'    => ['total' => $cities->count(), 'type' => 'all']
                ];
            }

            // Paginated for large queries
            $cities = $query->paginate(100);

            return [
                'success' => true,
                'data'    => CityResource::collection($cities->getCollection())->resolve($request),
                'meta'    => [
                    'current_page' => $cities->currentPage(),
                    'last_page'    => $cities->lastPage(),
                    'per_page'     => $cities->perPage(),
                    'total'        => $cities->total(),
                ]
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }

    public function show(City $city, Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('cities.show.v3', $request, function () use ($city, $request) {
            $city->load('state');

            return [
                'success' => true,
                'data'    => (new CityResource($city))->resolve($request),
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }
}