<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $include = $request->query('include', '');

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'code'          => $this->code,
            'iso3'          => $this->iso3,
            'phonecode'     => $this->phonecode,
            'flag'          => $this->flag,

            'states_count'  => $this->whenLoaded('states', fn() => $this->states->count()),
            'cities_count'  => $this->whenLoaded('cities', fn() => $this->cities->count()),

            // States : include all states wehen requested
            'states' => $this->when(
                str_contains($include, 'states'),
                fn() => StateResource::collection($this->states)
            ),

            // Cities (limited to avoid huge payload)
            'cities' => $this->when(
                str_contains($include, 'cities'),
                fn() => CityResource::collection($this->cities->take(50))
            ),
        ];
    }
}