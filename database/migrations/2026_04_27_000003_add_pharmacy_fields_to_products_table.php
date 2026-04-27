<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Nullify company_id when company is soft-deleted or hard-deleted.
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('companies')
                  ->nullOnDelete();

            // Barcode: nullable, indexed for fast lookup by scanner.
            $table->string('barcode')->nullable()->after('company_id');
            $table->index('barcode');

            $table->string('unit')->nullable()->after('barcode');   // e.g. tablet, vial, box
            $table->string('form')->nullable()->after('unit');      // e.g. syrup, tablet, injection
            $table->integer('min_stock')->default(0)->after('quantity'); // low-stock alert threshold
            $table->boolean('is_active')->default(true)->after('min_stock')->index();

            // Add soft-deletes only if the column does not already exist.
            if (! Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop FK constraint before dropping the column.
            $table->dropForeign(['company_id']);
            $table->dropIndex(['barcode']);
            $table->dropColumn([
                'company_id',
                'barcode',
                'unit',
                'form',
                'min_stock',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};

