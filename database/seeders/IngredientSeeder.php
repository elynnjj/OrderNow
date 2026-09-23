<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            [
                'name' => 'Beef Patty',
                'unit' => 'pcs',
                'current_stock' => 50,
                'reorder_level' => 10,
            ],
            [
                'name' => 'Burger Bun',
                'unit' => 'pcs',
                'current_stock' => 40,
                'reorder_level' => 10,
            ],
            [
                'name' => 'Cheese',
                'unit' => 'slice',
                'current_stock' => 60,
                'reorder_level' => 15,
            ],
            [
                'name' => 'Lettuce',
                'unit' => 'g',
                'current_stock' => 2000,
                'reorder_level' => 500,
            ],
            [
                'name' => 'Tomato',
                'unit' => 'g',
                'current_stock' => 1500,
                'reorder_level' => 400,
            ],
            [
                'name' => 'Fries',
                'unit' => 'g',
                'current_stock' => 300,
                'reorder_level' => 1000,
            ],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::updateOrCreate(
                ['name' => $ingredient['name']],
                $ingredient
            );
        }
    }
}
