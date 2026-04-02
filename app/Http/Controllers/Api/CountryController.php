<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\StateResource;
use App\Models\Country;
use App\Support\ApiResponseCache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('countries.index.v4', $request, function () use ($request) {
            $query = Country::query();

            // Search Logic
            if ($search = $request->query('search')) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('iso3', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "$search");
            }

            // Exact Name Search (New)
            if ($exact = $request->query('name')) {
                $query->where('name', $exact);
            }

            // Search by ID (New)
            if ($id = $request->query('id')) {
                $query->where('id', $id);
            }

            // Always sort by ID for consistent pagination
            $query->orderBy('id');

            $shouldPaginate = $request->has('page') || $request->has('per_page');

            if (! $shouldPaginate) {
                $countries = $query->get();

                return [
                    'success' => true,
                    'data'    => CountryResource::collection($countries)->resolve($request),
                    'meta'    => [
                        'total' => $countries->count(),
                        'type' => 'all',
                    ],
                ];
            }

            $countries = $query->paginate((int) $request->integer('per_page', 50));

            return [
                'success' => true,
                'data'    => CountryResource::collection($countries->getCollection())->resolve($request),
                'meta'    => [
                    'current_page' => $countries->currentPage(),
                    'last_page'    => $countries->lastPage(),
                    'per_page'     => $countries->perPage(),
                    'total'        => $countries->total(),
                    'from'         => $countries->firstItem(),
                    'to'           => $countries->lastItem(),
                ]
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }

    public function show(Country $country, Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('countries.show.v3', $request, function () use ($country, $request) {
            $includes = array_filter(explode(',', $request->query('include', '')));

            $load = [];
            if (in_array('states', $includes, true)) {
                $load[] = 'states';
            }

            if (in_array('cities', $includes, true)) {
                $load[] = 'cities';
            }

            if (!empty($load)) {
                $country->load($load);
            }

            return [
                'success' => true,
                'data'    => (new CountryResource($country))->resolve($request),
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }

    public function states(Country $country, Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('countries.states.v3', $request, function () use ($country, $request) {
            $query = $country->states()->orderBy('id');

            if ($search = $request->query('search')) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            $states = $query->paginate(100);

            return [
                'success' => true,
                'data'    => StateResource::collection($states->getCollection())->resolve($request),
                'meta'    => [
                    'current_page' => $states->currentPage(),
                    'last_page'    => $states->lastPage(),
                    'total'        => $states->total(),
                ],
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }
}
