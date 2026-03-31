<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    /**
     * Display a listing of states (with optional country filter)
     */
    public function index(Request $request): JsonResponse
    {
        $query = State::query();

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $states = $query->get();

        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }

    /**
     * Display a specific state with country info
     */
    public function show(State $state): JsonResponse
    {
        $state->load('country');

        return response()->json([
            'success' => true,
            'data' => $state
        ]);
    }
}