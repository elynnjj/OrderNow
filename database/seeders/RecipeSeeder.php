<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cheeseburger = MenuItem::where('name', 'Cheeseburger')->first();
        if ($cheeseburger) {
            $cheeseburgerIngredients = [
                'Beef Patty' => 1,
                'Burger Bun' => 1,
                'Cheese' => 1,
                'Lettuce' => 20,
                'Tomato' => 15,
            ];

            $attachData = [];
            foreach ($cheeseburgerIngredients as $ingredientName => $quantity) {
                $ingredient = Ingredient::where('name', $ingredientName)->first();
                if ($ingredient) {
                    $attachData[$ingredient->id] = ['quantity' => $quantity];
                }
            }

            if (!empty($attachData)) {
                $cheeseburger->ingredients()->syncWithoutDetaching($attachData);
            }
        }

        $friesItem = MenuItem::where('name', 'Fries')->first();
        if ($friesItem) {
            $friesIngredient = Ingredient::where('name', 'Fries')->first();
            if ($friesIngredient) {
                $friesItem->ingredients()->syncWithoutDetaching([
                    $friesIngredient->id => ['quantity' => 150],
                ]);
            }
        }
    }
}
