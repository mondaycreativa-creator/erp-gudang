<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('mbg_kitchen_usage_items')) {
            try {
                DB::statement("ALTER TABLE `mbg_kitchen_usage_items` MODIFY `product_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {
                try {
                    Schema::table('mbg_kitchen_usage_items', function (Blueprint $table) {
                        $table->unsignedBigInteger('product_id')->nullable()->change();
                    });
                } catch (\Throwable $e2) {}
            }

            Schema::table('mbg_kitchen_usage_items', function (Blueprint $table) {
                if (!Schema::hasColumn('mbg_kitchen_usage_items', 'product_name')) {
                    $table->string('product_name')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('mbg_kitchen_usage_items', 'unit_name')) {
                    $table->string('unit_name', 50)->nullable()->after('product_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mbg_kitchen_usage_items')) {
            Schema::table('mbg_kitchen_usage_items', function (Blueprint $table) {
                if (Schema::hasColumn('mbg_kitchen_usage_items', 'unit_name')) {
                    $table->dropColumn('unit_name');
                }
                if (Schema::hasColumn('mbg_kitchen_usage_items', 'product_name')) {
                    $table->dropColumn('product_name');
                }
            });
        }
    }
};
