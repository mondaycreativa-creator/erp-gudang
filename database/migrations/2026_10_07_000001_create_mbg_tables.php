<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mbg_dispatches')) {
            Schema::create('mbg_dispatches', function (Blueprint $table) {
                $table->id();
                $table->string('dispatch_code')->unique();
                $table->unsignedBigInteger('from_warehouse_id');
                $table->unsignedBigInteger('to_warehouse_id');
                $table->string('delivery_day'); // Senin, Selasa, Rabu, etc.
                $table->date('delivery_date');
                $table->string('status')->default('completed'); // sent, completed
                $table->string('payment_status')->default('unpaid'); // unpaid, paid, partial
                $table->decimal('total_price', 15, 2)->default(0); // Tagihan ke dapur
                $table->decimal('total_cost', 15, 2)->default(0);  // HPP / Modal Koperasi
                $table->decimal('cashback_percent', 5, 2)->default(0);
                $table->decimal('cashback_amount', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->integer('workspace')->default(1);
                $table->integer('created_by')->default(2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mbg_dispatch_items')) {
            Schema::create('mbg_dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dispatch_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 10, 2)->default(0);
                $table->decimal('purchase_price', 15, 2)->default(0);
                $table->decimal('sale_price', 15, 2)->default(0);
                $table->decimal('subtotal_cost', 15, 2)->default(0);
                $table->decimal('subtotal_price', 15, 2)->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->foreign('dispatch_id')->references('id')->on('mbg_dispatches')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('mbg_kitchen_usages')) {
            Schema::create('mbg_kitchen_usages', function (Blueprint $table) {
                $table->id();
                $table->string('usage_code')->unique();
                $table->unsignedBigInteger('warehouse_id'); // SPPG Pelutan / SPPG Kerep
                $table->date('usage_date');
                $table->string('meal_session')->default('Siang'); // Pagi, Siang, Sore
                $table->integer('portion_count')->default(0);
                $table->string('menu_name')->nullable();
                $table->text('notes')->nullable();
                $table->integer('workspace')->default(1);
                $table->integer('created_by')->default(2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mbg_kitchen_usage_items')) {
            Schema::create('mbg_kitchen_usage_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usage_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 10, 2)->default(0);
                $table->string('type')->default('standard'); // standard (dipakai), additional (barang tambahan)
                $table->decimal('additional_cost', 15, 2)->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->foreign('usage_id')->references('id')->on('mbg_kitchen_usages')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbg_kitchen_usage_items');
        Schema::dropIfExists('mbg_kitchen_usages');
        Schema::dropIfExists('mbg_dispatch_items');
        Schema::dropIfExists('mbg_dispatches');
    }
};
