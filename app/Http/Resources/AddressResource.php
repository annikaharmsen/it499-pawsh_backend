<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'street_one' => $this->street_one,
            'street_two' => $this->street_two,
            'city' => $this->city,
            'state' => $this->state,
            'postalcode' => $this->postalcode,
            'country' => $this->country,
            'name' => $this->name
        ];
    }
}
