<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'is_active'   => $this->is_active,
            'ingredients' => $this->ingredients ? $this->ingredients->map(function ($ingredient) {
                return [
                    'id'       => $ingredient->id,
                    'name'     => $ingredient->name,
                    'unit'     => $ingredient->unit,
                    'quantity' => (float) ($ingredient->pivot->quantity ?? 0),
                    'cost'     => null,
                ];
            }) : [],
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
