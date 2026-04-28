<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products.quantity is a legacy column (stock is now tracked via stock_movements).
 * It was originally defined without a default value (NOT NULL), which forces every
 * Product::create() call to supply it — even in tests where the value is meaningless.
 *
 * Adding a DB-level default of 0 lets us omit it from application code without
 * hitting constraint violations.  The column itself is NOT dropped here; that
 * deferred migration will follow once all legacy references are cleaned up.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->smallInteger('quantity')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove the default — restores the original no-default NOT NULL state.
            $table->smallInteger('quantity')->change();
        });
    }
};

