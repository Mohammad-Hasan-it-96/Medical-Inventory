<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_entries', function (Blueprint $table) {
            $table->id();

            // Core accounting record — restrict deletion of pharmacy to protect ledger integrity.
            $table->foreignId('pharmacy_id')
                  ->constrained('pharmacies')
                  ->restrictOnDelete();

            // Retain entries even if the linked order or payment is voided/deleted.
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();

            // payment_id is a soft reference only — no FK constraint since the payments
            // table may not exist yet and financial history must survive payment deletion.
            $table->unsignedBigInteger('payment_id')->nullable();

            $table->enum('type', ['debit', 'credit'])->index(); // debit = owes us, credit = we owe / received
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->date('entry_date')->index();

            // Preserve ledger history after a user account is removed.
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('pharmacy_id');
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_entries');
    }
};

