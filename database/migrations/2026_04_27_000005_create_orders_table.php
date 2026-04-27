<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->nullable(); // human-readable reference, auto-generated after creation

            // Prevent hard-deleting a pharmacy that has orders (use soft-delete instead).
            $table->foreignId('pharmacy_id')
                  ->constrained('pharmacies')
                  ->restrictOnDelete();

            // If the rep account is removed, keep the order history intact.
            $table->foreignId('rep_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('status', ['draft', 'pending', 'confirmed', 'cancelled'])
                  ->default('pending')
                  ->index();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pharmacy_id');
            $table->index('rep_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

