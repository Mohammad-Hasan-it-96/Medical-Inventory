<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Financial record — restrict hard-delete of pharmacy to avoid orphan payments.
            $table->foreignId('pharmacy_id')
                  ->constrained('pharmacies')
                  ->restrictOnDelete();

            // Payment may be a general payment not tied to a specific order; nullify if order deleted.
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();

            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'bank', 'other'])->default('cash')->index();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable()->index();

            // Keep audit trail even if the user account is removed.
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('pharmacy_id');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

