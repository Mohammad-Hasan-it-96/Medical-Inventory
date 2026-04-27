<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'phone'           => $this->phone,
            'address'         => $this->address,
            'area'            => $this->area,
            'credit_limit'    => $this->credit_limit,
            'opening_balance' => $this->opening_balance,

            // balance is computed outside (AccountEntry sum).
            // Controller should set:  $pharmacy->balance = $computed;
            'balance' => $this->when(
                $this->resource->getAttribute('balance') !== null,
                fn () => (float) $this->resource->getAttribute('balance'),
            ),

            // Assigned sales rep — only present when eager-loaded.
            'rep' => $this->whenLoaded('rep', fn () => [
                'id'   => $this->rep->id,
                'name' => $this->rep->name,
            ]),

            'is_active'  => $this->is_active,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}

