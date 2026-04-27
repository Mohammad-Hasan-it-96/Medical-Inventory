<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'quantity'   => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount'   => $this->discount,
            'total'      => $this->total,

            // Product detail — only present when eager-loaded.
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

