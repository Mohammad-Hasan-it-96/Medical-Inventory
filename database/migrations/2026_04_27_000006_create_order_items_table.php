<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Items are a part of the order; cascade delete is safe here.
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            // Prevent deleting a product that still appears in order lines.
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

