<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'quantity'       => $this->quantity,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'notes'          => $this->notes,
            'created_by'     => $this->created_by,
            'created_at'     => $this->created_at?->toIso8601String(),

            // Product detail — only present when eager-loaded.
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

