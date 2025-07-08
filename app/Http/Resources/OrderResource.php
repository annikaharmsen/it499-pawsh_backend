<?php

namespace App\Http\Resources;

use App\services\OrderService;
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
        return [
            'id' => $this->id,
            'total' => OrderService::getTotal($this),
            'orderdate' => $this->orderdate,
            'status' => $this->status,
            'user' => $this->user,
            'shipping_address' => $this->shipping_address,
            'items' => $this->items,
            'payments' => $this->payments
        ];
    }
}
