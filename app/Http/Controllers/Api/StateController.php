<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use App\Support\ApiResponseCache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class StateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('states.index.v3', $request, function () use ($request) {
            $query = State::query()->orderBy('name');

            if ($countryId = $request->query('country_id')) {
                $query->where('country_id', $countryId);
            }

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
                    'per_page'     => $states->perPage(),
                    'total'        => $states->total(),
                ]
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }

    public function show(State $state, Request $request): JsonResponse
    {
        $payload = ApiResponseCache::remember('states.show.v3', $request, function () use ($state, $request) {
            $includes = array_filter(explode(',', $request->query('include', '')));

            $load = [];
            if (in_array('country', $includes, true)) {
                $load[] = 'country';
            }

            if (in_array('cities', $includes, true)) {
                $load[] = 'cities';
            }

            if (!empty($load)) {
                $state->load($load);
            }

            return [
                'success' => true,
                'data' => (new StateResource($state))->resolve($request),
            ];
        });

        return ApiResponseCache::toResponse($request, $payload);
    }
}