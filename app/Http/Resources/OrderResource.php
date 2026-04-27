<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'subtotal'     => $this->subtotal,
            'discount'     => $this->discount,
            'total'        => $this->total,
            'notes'        => $this->notes,

            // Timestamps — ISO 8601 strings for easy parsing in Flutter/Dart.
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),

            // Relationships — only present when eager-loaded to prevent N+1.
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),

            'rep' => $this->whenLoaded('rep', fn () => [
                'id'   => $this->rep->id,
                'name' => $this->rep->name,
            ]),

            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}

