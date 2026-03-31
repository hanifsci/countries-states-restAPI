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

        // Search
        if ($search = $request->query('search')) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('iso3', 'LIKE', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $countries = $query->paginate(50);   // 50 per page

        return response()->json([
            'success' => true,
            'data'    => CountryResource::collection($countries),
            'meta'    => [
                'current_page' => $countries->currentPage(),
                'last_page'    => $countries->lastPage(),
                'per_page'     => $countries->perPage(),
                'total'        => $countries->total(),
            ]
        ]);
    }

    public function show(Country $country): JsonResponse
    {
        $country->load('states');

        return response()->json([
            'success' => true,
            'data'    => new CountryResource($country)
        ]);
    }

    public function states(Country $country, Request $request): JsonResponse
    {
        $states = $country->states()
                          ->when($request->search, fn($q, $search) => 
                              $q->where('name', 'LIKE', "%{$search}%")
                          )
                          ->paginate(100);

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