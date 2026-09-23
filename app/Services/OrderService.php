<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    /**
     * Create a new pending order.
     */
    public function createOrder(array $data): Order
    {
        if (! empty($data['idempotency_key'])) {
            $existingOrder = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existingOrder) {
                return $existingOrder->load(['items.menuItem']);
            }
        }

        return DB::transaction(function () use ($data) {
            $orderNumber = null;
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $candidate = '#' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                if (! Order::where('order_number', $candidate)->exists()) {
                    $orderNumber = $candidate;
                    break;
                }
            }

            if (! $orderNumber) {
                throw new RuntimeException('Failed to generate unique order number.');
            }

            $order = Order::create([
                'order_number'    => $orderNumber,
                'status'          => 'pending',
                'total'           => 0,
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (! $menuItem || ! $menuItem->is_active) {
                    throw new InvalidArgumentException("Menu item ID {$itemData['menu_item_id']} is invalid or inactive.");
                }

                $unitPrice = (float) $menuItem->price;
                $quantity = (int) $itemData['quantity'];
                $subtotal = $unitPrice * $quantity;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity'     => $quantity,
                    'unit_price'   => $unitPrice,
                    'subtotal'     => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total' => $total]);

            return $order->fresh(['items.menuItem']);
        });
    }

    /**
     * Confirm an order and deduct inventory atomically.
     */
    public function confirmOrder(Order $order): Order
    {
        if ($order->status === 'confirmed') {
            return $order->load(['items.menuItem']);
        }

        if ($order->status === 'cancelled') {
            throw new InvalidArgumentException('Cannot confirm a cancelled order.');
        }

        return DB::transaction(function () use ($order) {
            $order->load(['items.menuItem.recipeIngredients']);

            $requiredIngredients = [];
            foreach ($order->items as $orderItem) {
                $menuItem = $orderItem->menuItem;
                if (! $menuItem) {
                    continue;
                }

                foreach ($menuItem->recipeIngredients as $recipeIngredient) {
                    $ingId = $recipeIngredient->ingredient_id;
                    $qty = (float) $recipeIngredient->quantity * (int) $orderItem->quantity;
                    $requiredIngredients[$ingId] = ($requiredIngredients[$ingId] ?? 0) + $qty;
                }
            }

            $ids = array_keys($requiredIngredients);
            sort($ids);

            if (empty($ids)) {
                $order->status = 'confirmed';
                $order->save();
                return $order->fresh(['items.menuItem']);
            }

            $ingredients = Ingredient::whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($ids as $id) {
                $requiredQty = $requiredIngredients[$id];
                $ingredient = $ingredients->get($id);

                if (! $ingredient || (float) $ingredient->current_stock < $requiredQty) {
                    throw new InsufficientStockException(
                        "Not enough {$ingredient?->name}.",
                        [
                            'ingredient_id'   => $id,
                            'ingredient_name' => $ingredient?->name ?? 'Unknown',
                            'required'        => (float) $requiredQty,
                            'available'       => (float) ($ingredient?->current_stock ?? 0),
                            'unit'            => $ingredient?->unit ?? '',
                        ]
                    );
                }
            }

            foreach ($ids as $id) {
                $requiredQty = $requiredIngredients[$id];
                $ingredient = $ingredients->get($id);

                $newStock = (float) $ingredient->current_stock - $requiredQty;
                $ingredient->current_stock = $newStock;
                $ingredient->save();

                InventoryTransaction::create([
                    'ingredient_id' => $id,
                    'change'        => -$requiredQty,
                    'reason'        => 'order',
                    'reference_id'  => $order->id,
                    'stock_after'   => $newStock,
                ]);
            }

            $order->status = 'confirmed';
            $order->save();

            return $order->fresh(['items.menuItem']);
        });
    }

    /**
     * Cancel an unconfirmed order.
     */
    public function cancelOrder(Order $order): Order
    {
        if ($order->status === 'confirmed') {
            throw new InvalidArgumentException('Cannot cancel a confirmed order.');
        }

        $order->status = 'cancelled';
        $order->save();

        return $order->fresh(['items.menuItem']);
    }
}
