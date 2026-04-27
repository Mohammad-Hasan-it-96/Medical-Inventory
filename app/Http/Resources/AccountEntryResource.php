<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'pharmacy_id' => $this->pharmacy_id,
            'order_id'    => $this->order_id,
            'payment_id'  => $this->payment_id,
            'type'        => $this->type,           // debit | credit
            'amount'      => $this->amount,
            'description' => $this->description,
            'entry_date'  => $this->entry_date?->toDateString(), // date only (YYYY-MM-DD)
            'created_by'  => $this->created_by,
            'created_at'  => $this->created_at?->toIso8601String(),

            // Relationships — only present when eager-loaded.
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
        ];
    }
}

