<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();

            // Price records are meaningless without the product; cascade is appropriate.
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            $table->decimal('net_price_syp', 15, 2)->default(0);    // cost / net price in SYP
            $table->decimal('public_price_syp', 15, 2)->default(0); // public/retail price in SYP

            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};

