<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'barcode' => $this->barcode,
            'unit'    => $this->unit,
            'form'    => $this->form,

            // Nested company — only present when relation is eager-loaded.
            'company' => new CompanyResource($this->whenLoaded('company')),

            // Prices — only present when the `price` relation is eager-loaded.
            'net_price_syp'    => $this->whenLoaded('price', fn () => $this->price?->net_price_syp),
            'public_price_syp' => $this->whenLoaded('price', fn () => $this->price?->public_price_syp),

            // current_stock is NOT calculated here to prevent N+1.
            // To include it, set a temporary attribute in the controller:
            //   $product->current_stock = $stockService->getCurrentStock($product->id);
            'current_stock' => $this->when(
                $this->resource->getAttribute('current_stock') !== null,
                fn () => (int) $this->resource->getAttribute('current_stock'),
            ),

            'is_active'  => $this->is_active,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
