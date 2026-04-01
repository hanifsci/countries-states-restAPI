<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StateResource;

class CountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
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

        $countries = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => CountryResource::collection($countries),
            'meta'    => [
                'current_page' => $countries->currentPage(),
                'last_page'    => $countries->lastPage(),
                'per_page'     => $countries->perPage(),
                'total'        => $countries->total(),
                'from'         => $countries->firstItem(),
                'to'           => $countries->lastItem(),
            ]
        ]);
    }

    public function show(Country $country, Request $request): JsonResponse
    {
        $includes = explode(',', $request->query('include', ''));

        $load = [];

        if (in_array('states', $includes)) {
            $load[] = 'states';
        }
        if (in_array('cities', $includes)) {
            $load[] = 'cities';
        }

        if (!empty($load)) {
            $country->load($load);
        }

        return response()->json([
            'success' => true,
            'data'    => new CountryResource($country)
        ]);
    }

    public function states(Country $country, Request $request): JsonResponse
    {
        $query = $country->states()->orderBy('id');

        if ($search = $request->query('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $states = $query->paginate(100);

        return response()->json([
            'success' => true,
            'data'    => StateResource::collection($states),
            'meta'    => [
                'current_page' => $states->currentPage(),
                'last_page'    => $states->lastPage(),
                'total'        => $states->total(),
            ]
        ]);
    }
}