<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'total'        => (float) $this->total,
            'items'        => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id'             => $item->id,
                        'menu_item_id'   => $item->menu_item_id,
                        'menu_item_name' => $item->menuItem?->name,
                        'quantity'       => (int) $item->quantity,
                        'unit_price'     => (float) $item->unit_price,
                        'subtotal'       => (float) $item->subtotal,
                    ];
                });
            }),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
