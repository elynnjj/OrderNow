<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('change', 10, 3);
            $table->enum('reason', ['order', 'adjustment', 'waste', 'restock']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('stock_after', 10, 3);
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
            $table->index(['reason', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};

