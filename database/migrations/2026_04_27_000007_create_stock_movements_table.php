<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Prevent deleting a product with existing stock history.
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();

            $table->enum('type', [
                'opening',      // initial opening stock entry
                'purchase',     // stock received from supplier
                'sale',         // stock consumed by a confirmed order
                'sale_cancel',  // reversal when an order is cancelled
                'adjustment',   // manual warehouse correction
                'return_in',    // goods returned to warehouse
                'return_out',   // goods returned to supplier
            ])->index();

            $table->integer('quantity'); // positive = in, negative = out

            // Polymorphic-style soft reference (e.g. orders, purchase_orders) — no FK constraint
            // intentionally so movements survive if the source record is archived.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id']);

            $table->text('notes')->nullable();

            // Keep audit trail even if the user account is later removed.
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('product_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

