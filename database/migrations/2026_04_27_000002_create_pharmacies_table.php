<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('area')->nullable()->index();
            // Nullify rep_id when the user (rep) is deleted, keep the pharmacy.
            $table->foreignId('rep_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('rep_id');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
