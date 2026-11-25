<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_products') || !Schema::hasTable('warehouses')) {
            return;
        }

        // 1. Gabungkan & hapus baris duplikat pada warehouse_products untuk (warehouse_id, product_id) yang sama
        $duplicateGroups = DB::table('warehouse_products')
            ->select('warehouse_id', 'product_id', DB::raw('COUNT(*) as total'))
            ->groupBy('warehouse_id', 'product_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            $rows = DB::table('warehouse_products')
                ->where('warehouse_id', $group->warehouse_id)
                ->where('product_id', $group->product_id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            if ($rows->count() <= 1) {
                continue;
            }

            $keepRow = $rows->first();
            $deleteIds = $rows->slice(1)->pluck('id')->all();

            if (!empty($deleteIds)) {
                DB::table('warehouse_products')->whereIn('id', $deleteIds)->delete();
            }

            // Pastikan workspace terisi jika sempat kosong
            if (empty($keepRow->workspace)) {
                $whWorkspace = DB::table('warehouses')->where('id', $group->warehouse_id)->value('workspace');
                if ($whWorkspace) {
                    DB::table('warehouse_products')->where('id', $keepRow->id)->update(['workspace' => $whWorkspace]);
                }
            }
        }

        // 2. Sinkronkan stok master product_services dengan stok fisik Gudang Pusat per workspace
        if (Schema::hasTable('product_services')) {
            $workspaces = DB::table('warehouses')
                ->whereNotNull('workspace')
                ->distinct()
                ->pluck('workspace');

            foreach ($workspaces as $workspace) {
                $pusat = DB::table('warehouses')
                    ->where('workspace', $workspace)
                    ->where('name', 'like', '%Pusat%')
                    ->orderBy('id', 'asc')
                    ->first()
                    ?? DB::table('warehouses')
                        ->where('workspace', $workspace)
                        ->where('name', 'like', '%Koperasi%')
                        ->orderBy('id', 'asc')
                        ->first()
                    ?? DB::table('warehouses')
                        ->where('workspace', $workspace)
                        ->orderBy('id', 'asc')
                        ->first();

                if (!$pusat) {
                    continue;
                }

                $pusatStocks = DB::table('warehouse_products')
                    ->where('warehouse_id', $pusat->id)
                    ->pluck('quantity', 'product_id');

                $trackedProductIds = DB::table('warehouse_products')
                    ->where('workspace', $workspace)
                    ->distinct()
                    ->pluck('product_id');

                foreach ($trackedProductIds as $productId) {
                    $qty = isset($pusatStocks[$productId]) ? (float) $pusatStocks[$productId] : 0;
                    DB::table('product_services')
                        ->where('id', $productId)
                        ->update(['quantity' => $qty]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data deduplication is not reversible
    }
};
