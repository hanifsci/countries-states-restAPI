<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $states = State::query()
            ->when($request->country_id, fn($q) => $q->where('country_id', $request->country_id))
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