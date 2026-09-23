<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MenuItemController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = MenuItem::with('ingredients')->get();

        return MenuItemResource::collection($items);
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menuItem = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $ingredientsData = $validated['ingredients'];
            unset($validated['ingredients']);

            $menuItem = MenuItem::create($validated);

            $mapped = [];
            foreach ($ingredientsData as $ing) {
                $mapped[$ing['ingredient_id']] = ['quantity' => $ing['quantity']];
            }

            $menuItem->ingredients()->sync($mapped);

            return $menuItem->load('ingredients');
        });

        return (new MenuItemResource($menuItem))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MenuItem $menuItem): MenuItemResource
    {
        return new MenuItemResource($menuItem->load('ingredients'));
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): MenuItemResource
    {
        $updatedMenuItem = DB::transaction(function () use ($request, $menuItem) {
            $validated = $request->validated();

            if (array_key_exists('ingredients', $validated)) {
                $ingredientsData = $validated['ingredients'];
                unset($validated['ingredients']);

                $mapped = [];
                foreach ($ingredientsData as $ing) {
                    $mapped[$ing['ingredient_id']] = ['quantity' => $ing['quantity']];
                }

                $menuItem->ingredients()->sync($mapped);
            }

            if (! empty($validated)) {
                $menuItem->update($validated);
            }

            return $menuItem->load('ingredients');
        });

        return new MenuItemResource($updatedMenuItem);
    }

    public function destroy(MenuItem $menuItem): Response
    {
        $menuItem->delete();

        return response()->noContent();
    }
}
