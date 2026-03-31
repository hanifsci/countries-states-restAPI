<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'code'      => $this->code,
            'iso3'      => $this->iso3,
            'phonecode' => $this->phonecode,
            'flag'      => $this->flag,
            'states_count' => $this->whenLoaded('states', fn() => $this->states->count()),
        ];
    }
}
