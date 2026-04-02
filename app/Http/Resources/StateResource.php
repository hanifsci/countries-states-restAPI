<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $include = $request->query('include', '');

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'country_id'  => $this->country_id,
            'cities_count' => $this->whenLoaded('cities', fn() => $this->cities->count()),
            'country'     => $this->whenLoaded('country', fn() => [
                'id'   => $this->country->id,
                'name' => $this->country->name,
                'code' => $this->country->code,
            ]),
            'cities'      => $this->when(
                str_contains($include, 'cities'),
                fn() => CityResource::collection($this->cities)->resolve($request)
            ),
        ];
    }
}
