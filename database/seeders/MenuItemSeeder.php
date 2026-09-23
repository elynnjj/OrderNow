<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Cheeseburger',
                'price' => 18.00,
                'is_active' => true,
                'description' => 'Juicy beef patty with cheese, lettuce, and tomato',
            ],
            [
                'name' => 'Fries',
                'price' => 8.00,
                'is_active' => true,
                'description' => 'Crispy golden fries',
            ],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
