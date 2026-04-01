<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = City::query()->orderBy('id');

        // Filter by state
        if ($stateId = $request->query('state_id')) {
            $query->where('state_id', $stateId);
        }

        // Filter by country (via state)
        if ($countryId = $request->query('country_id')) {
            $query->whereHas('state', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }

        // Search by city name
        if ($search = $request->query('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $cities = $query->paginate(500);   // Safe pagination for large table

        return response()->json([
            'success' => true,
            'data'    => CityResource::collection($cities),
            'meta'    => [
                'current_page' => $cities->currentPage(),
                'last_page'    => $cities->lastPage(),
                'per_page'     => $cities->perPage(),
                'total'        => $cities->total(),
            ]
        ]);
    }

    public function show(City $city): JsonResponse
    {
        $city->load('state');

        return response()->json([
            'success' => true,
            'data'    => new CityResource($city)
        ]);
    }
}