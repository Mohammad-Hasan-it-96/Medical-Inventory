<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * product_prices.product_id must be unique because Product::productPrice()
 * is a hasOne relationship.  Without a DB-level unique constraint a
 * double-insert would silently create a second price row and leave the first
 * one inaccessible, leading to stale/ghost price data.
 *
 * Before adding the constraint we collapse any existing duplicates to keep
 * only the most-recently-updated row per product.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate: keep only the row with the largest id per product.
        // Use driver-aware syntax so this works on both MySQL (production) and
        // SQLite (test environment).
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite supports DELETE with a subquery but not multi-table DELETE.
            DB::statement('
                DELETE FROM product_prices
                WHERE id NOT IN (
                    SELECT MAX(id)
                    FROM product_prices
                    GROUP BY product_id
                )
            ');
        } else {
            // MySQL / MariaDB multi-table DELETE.
            DB::statement('
                DELETE pp1
                FROM product_prices pp1
                INNER JOIN product_prices pp2
                    ON pp1.product_id = pp2.product_id
                   AND pp1.id < pp2.id
            ');
        }

        Schema::table('product_prices', function (Blueprint $table) {
            // MySQL will not allow dropping the plain index while a FK constraint
            // references it.  Drop the FK first, swap plain index → unique index,
            // then restore the FK (unique index satisfies the FK requirement too).
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->unique('product_id');
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropUnique(['product_id']);
            $table->index('product_id');
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->cascadeOnDelete();
        });
    }
};

