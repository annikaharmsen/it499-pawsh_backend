<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total = $this->items->reduce(function ($carry, $item) {
            return $carry += $item->price * $item->quantity;
        });

        return [
            'id' => $this->id,
            'total' => $total,
            'user' => $this->user,
            'address' => $this->address,
            'items' => $this->items,
            'payments' => $this->payments
        ];
    }
}
