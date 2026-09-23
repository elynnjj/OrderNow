<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('unit', ['g', 'kg', 'ml', 'l', 'pcs', 'slice']);
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('reorder_level', 10, 3)->default(0);
            $table->timestamps();

            $table->index('current_stock');
        });

        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT chk_ingredients_stock_non_negative CHECK (current_stock >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};

