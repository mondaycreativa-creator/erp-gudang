<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MbgDispatch;
use App\Models\MbgKitchenUsage;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use Modules\ProductService\Entities\ProductService;

class MbgTransactionsSampleSeeder extends Seeder
{
    public function run()
    {
        \Illuminate\Support\Facades\Auth::loginUsingId(2);

        $koperasi = Warehouse::where('name', 'like', '%Koperasi%')->first();
        $pelutan = Warehouse::where('name', 'like', '%Pelutan%')->first();
        $kerep = Warehouse::where('name', 'like', '%Kerep%')->first();

        if (!$koperasi || !$pelutan || !$kerep) return;

        $pBeras = ProductService::where('sku', 'MBG-BRS-001')->first();
        $pAyam = ProductService::where('sku', 'MBG-AYM-001')->first();
        $pTelur = ProductService::where('sku', 'MBG-TLR-001')->first();
        $pWortel = ProductService::where('sku', 'MBG-WRT-001')->first();
        $pBrokoli = ProductService::where('sku', 'MBG-BRK-001')->first();
        $pBawangM = ProductService::where('sku', 'MBG-BWM-001')->first();
        $pBawangP = ProductService::where('sku', 'MBG-BWP-001')->first();
        $pMinyak = ProductService::where('sku', 'MBG-MYK-001')->first();
        $pTempe = ProductService::where('sku', 'MBG-TMP-001')->first();
        $pPisang = ProductService::where('sku', 'MBG-PSG-001')->first();

        // 1. Dispatch Senin ke Pelutan
        if (!MbgDispatch::where('dispatch_code', 'MBG-KRM-20261005-001')->exists()) {
            $items1 = [
                ['product' => $pBeras, 'qty' => 150],
                ['product' => $pAyam, 'qty' => 45],
                ['product' => $pTelur, 'qty' => 500],
                ['product' => $pWortel, 'qty' => 25],
                ['product' => $pBawangM, 'qty' => 10],
                ['product' => $pBawangP, 'qty' => 8],
                ['product' => $pMinyak, 'qty' => 20],
            ];

            $totalPrice = 0;
            $totalCost = 0;
            foreach ($items1 as $it) {
                $totalPrice += $it['qty'] * $it['product']->sale_price;
                $totalCost += $it['qty'] * $it['product']->purchase_price;
            }
            $cashback = ($totalPrice * 2.5) / 100;

            $d1 = MbgDispatch::create([
                'dispatch_code' => 'MBG-KRM-20261005-001',
                'from_warehouse_id' => $koperasi->id,
                'to_warehouse_id' => $pelutan->id,
                'delivery_day' => 'Senin',
                'delivery_date' => '2026-10-05',
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_price' => $totalPrice,
                'total_cost' => $totalCost,
                'cashback_percent' => 2.5,
                'cashback_amount' => $cashback,
                'paid_amount' => $totalPrice,
                'notes' => 'Pengiriman stok mingguan tahap 1 untuk menu Senin-Rabu SPPG Pelutan',
                'workspace' => 1,
                'created_by' => 2,
            ]);

            foreach ($items1 as $it) {
                $d1->items()->create([
                    'product_id' => $it['product']->id,
                    'quantity' => $it['qty'],
                    'purchase_price' => $it['product']->purchase_price,
                    'sale_price' => $it['product']->sale_price,
                    'subtotal_cost' => $it['qty'] * $it['product']->purchase_price,
                    'subtotal_price' => $it['qty'] * $it['product']->sale_price,
                ]);

                WarehouseTransfer::warehouse_transfer_qty($koperasi->id, $pelutan->id, $it['product']->id, $it['qty']);
            }
        }

        // 2. Dispatch Selasa ke Kerep
        if (!MbgDispatch::where('dispatch_code', 'MBG-KRM-20261006-001')->exists()) {
            $items2 = [
                ['product' => $pBeras, 'qty' => 160],
                ['product' => $pAyam, 'qty' => 50],
                ['product' => $pBrokoli, 'qty' => 20],
                ['product' => $pWortel, 'qty' => 30],
                ['product' => $pTempe, 'qty' => 100],
                ['product' => $pPisang, 'qty' => 35],
            ];

            $totalPrice = 0;
            $totalCost = 0;
            foreach ($items2 as $it) {
                $totalPrice += $it['qty'] * $it['product']->sale_price;
                $totalCost += $it['qty'] * $it['product']->purchase_price;
            }
            $cashback = ($totalPrice * 2.5) / 100;

            $d2 = MbgDispatch::create([
                'dispatch_code' => 'MBG-KRM-20261006-001',
                'from_warehouse_id' => $koperasi->id,
                'to_warehouse_id' => $kerep->id,
                'delivery_day' => 'Selasa',
                'delivery_date' => '2026-10-06',
                'status' => 'completed',
                'payment_status' => 'unpaid', // Dapur Kerep belum terbayar (piutang)
                'total_price' => $totalPrice,
                'total_cost' => $totalCost,
                'cashback_percent' => 2.5,
                'cashback_amount' => $cashback,
                'paid_amount' => 0,
                'notes' => 'Pengiriman stok menu Selasa-Kamis SPPG Kerep Kemiri',
                'workspace' => 1,
                'created_by' => 2,
            ]);

            foreach ($items2 as $it) {
                $d2->items()->create([
                    'product_id' => $it['product']->id,
                    'quantity' => $it['qty'],
                    'purchase_price' => $it['product']->purchase_price,
                    'sale_price' => $it['product']->sale_price,
                    'subtotal_cost' => $it['qty'] * $it['product']->purchase_price,
                    'subtotal_price' => $it['qty'] * $it['product']->sale_price,
                ]);

                WarehouseTransfer::warehouse_transfer_qty($koperasi->id, $kerep->id, $it['product']->id, $it['qty']);
            }
        }

        // 3. Catatan Masak Dapur Pelutan
        if (!MbgKitchenUsage::where('usage_code', 'MBG-MSK-20261006-001')->exists()) {
            $u1 = MbgKitchenUsage::create([
                'usage_code' => 'MBG-MSK-20261006-001',
                'warehouse_id' => $pelutan->id,
                'usage_date' => '2026-10-06',
                'meal_session' => 'Makan Siang',
                'portion_count' => 1500,
                'menu_name' => 'Nasi Putih, Sop Ayam Wortel, Telur Rebus',
                'notes' => 'Penyaluran tepat waktu ke SDN 01 & 02 Pelutan',
                'workspace' => 1,
                'created_by' => 2,
            ]);

            $u1->items()->createMany([
                ['product_id' => $pBeras->id, 'quantity' => 75, 'type' => 'standard'],
                ['product_id' => $pAyam->id, 'quantity' => 25, 'type' => 'standard'],
                ['product_id' => $pTelur->id, 'quantity' => 250, 'type' => 'standard'],
                ['product_id' => $pWortel->id, 'quantity' => 15, 'type' => 'standard'],
                ['product_id' => $pBawangM->id, 'quantity' => 2, 'type' => 'standard'],
                ['product_id' => $pBawangP->id, 'quantity' => 1.5, 'type' => 'additional', 'additional_cost' => 35000, 'notes' => 'Beli tambahan di warung terdekat SPPG'],
            ]);

            // Deduct stock in Pelutan
            foreach ([
                [$pBeras->id, 75],
                [$pAyam->id, 25],
                [$pTelur->id, 250],
                [$pWortel->id, 15],
                [$pBawangM->id, 2],
            ] as [$pid, $qty]) {
                $whp = WarehouseProduct::where('warehouse_id', $pelutan->id)->where('product_id', $pid)->first();
                if ($whp) {
                    $whp->quantity = max(0, $whp->quantity - $qty);
                    $whp->save();
                }
            }
        }
    }
}
