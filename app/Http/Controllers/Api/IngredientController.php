<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return IngredientResource::collection(Ingredient::all());
    }

    public function store(StoreIngredientRequest $request): JsonResponse
    {
        $ingredient = Ingredient::create($request->validated());

        return (new IngredientResource($ingredient))
            ->response()
            ->setStatusCode(201);
    }

    public function adjust(AdjustStockRequest $request, Ingredient $ingredient): IngredientResource
    {
        $updatedIngredient = DB::transaction(function () use ($request, $ingredient) {
            $lockedIngredient = Ingredient::where('id', $ingredient->id)
                ->lockForUpdate()
                ->first();

            $change = (float) $request->validated('change');
            $reason = $request->validated('reason');

            $newStock = (float) $lockedIngredient->current_stock + $change;
            $lockedIngredient->current_stock = $newStock;
            $lockedIngredient->save();

            InventoryTransaction::create([
                'ingredient_id' => $lockedIngredient->id,
                'change'        => $change,
                'reason'        => $reason,
                'reference_id'  => null,
                'stock_after'   => $newStock,
            ]);

            return $lockedIngredient;
        });

        return new IngredientResource($updatedIngredient);
    }

    public function lowStock(): AnonymousResourceCollection
    {
        return IngredientResource::collection(Ingredient::lowStock()->get());
    }
}
