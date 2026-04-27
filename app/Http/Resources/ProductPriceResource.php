<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'product_id'       => $this->product_id,
            'net_price_syp'    => $this->net_price_syp,
            'public_price_syp' => $this->public_price_syp,
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}

