<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Country;

class CountryController extends Controller
{
   /**
     * Display a listing of all countries
     */
    public function index(): JsonResponse
    {
        $countries = Country::all();
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Display a specific country with its states
     */
    public function show(Country $country): JsonResponse
    {
        $country->load('states');
        
        return response()->json([
            'success' => true,
            'data' => $country
        ]);
    }

    /**
     * Get all states of a specific country
     */
    public function states(Country $country): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $country->states
        ]);
    }
}
