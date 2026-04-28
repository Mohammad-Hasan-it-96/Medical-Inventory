<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'pharmacy_id' => $this->pharmacy_id,
            'order_id'    => $this->order_id,
            'amount'      => $this->amount,
            'method'      => $this->method,
            'notes'       => $this->notes,
            'paid_at'     => $this->paid_at?->toIso8601String(),
            'created_by'  => $this->created_by,
            'created_at'  => $this->created_at?->toIso8601String(),

            // Relationships — only present when eager-loaded.
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'order'    => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
