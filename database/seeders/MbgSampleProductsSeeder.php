<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProductService\Entities\ProductService;
use Modules\ProductService\Entities\Unit;
use Modules\ProductService\Entities\Category;
use App\Models\WarehouseProduct;
use App\Models\User;

class MbgSampleProductsSeeder extends Seeder
{
    public function run()
    {
        $company = User::where('type', 'company')->orderBy('id', 'asc')->first();
        $companyId = $company ? $company->id : 2;
        $workspaceId = $company && $company->active_workspace ? $company->active_workspace : 1;

        // 1. Units
        $unitNames = ['Kg', 'Gram', 'Liter', 'Butir', 'Ikat', 'Pcs / Buah', 'Dus / Karton', 'Karung'];
        $units = [];
        foreach ($unitNames as $uName) {
            $u = Unit::firstOrCreate(
                ['name' => $uName, 'created_by' => $companyId, 'workspace_id' => $workspaceId]
            );
            $units[$uName] = $u->id;
        }

        // 2. Categories
        $catVeg = Category::where('name', 'Sayuran & Buah Segar')->where('workspace_id', $workspaceId)->first();
        $catProtein = Category::where('name', 'Lauk & Protein Hewani/Nabati')->where('workspace_id', $workspaceId)->first();
        $catSpices = Category::where('name', 'Bumbu & Rempah Dapur')->where('workspace_id', $workspaceId)->first();
        $catStaple = Category::where('name', 'Sembako & Karbohidrat')->where('workspace_id', $workspaceId)->first();

        // 3. Products
        $products = [
            [
                'name' => 'Beras Premium Ramos',
                'sku' => 'MBG-BRS-001',
                'sale_price' => 15000,
                'purchase_price' => 13500,
                'tax_id' => null,
                'category_id' => $catStaple ? $catStaple->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Beras pulen standar MBG',
                'initial_stock' => 1000, // 1000 Kg di Gudang Koperasi
            ],
            [
                'name' => 'Daging Ayam Fillet Dada',
                'sku' => 'MBG-AYM-001',
                'sale_price' => 38000,
                'purchase_price' => 34000,
                'tax_id' => null,
                'category_id' => $catProtein ? $catProtein->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Ayam potong segar harian',
                'initial_stock' => 250, // 250 Kg
            ],
            [
                'name' => 'Telur Ayam Negeri',
                'sku' => 'MBG-TLR-001',
                'sale_price' => 2200,
                'purchase_price' => 1900,
                'tax_id' => null,
                'category_id' => $catProtein ? $catProtein->id : 0,
                'unit_id' => $units['Butir'],
                'type' => 'product',
                'description' => 'Telur ayam grade A',
                'initial_stock' => 3000, // 3000 Butir
            ],
            [
                'name' => 'Wortel Segar Brastagi',
                'sku' => 'MBG-WRT-001',
                'sale_price' => 14000,
                'purchase_price' => 11500,
                'tax_id' => null,
                'category_id' => $catVeg ? $catVeg->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Wortel segar pilihan',
                'initial_stock' => 150, // 150 Kg
            ],
            [
                'name' => 'Brokoli Hijau Super',
                'sku' => 'MBG-BRK-001',
                'sale_price' => 22000,
                'purchase_price' => 18000,
                'tax_id' => null,
                'category_id' => $catVeg ? $catVeg->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Sayuran hijau nutrisi tinggi',
                'initial_stock' => 100, // 100 Kg
            ],
            [
                'name' => 'Bawang Merah Brebes',
                'sku' => 'MBG-BWM-001',
                'sale_price' => 32000,
                'purchase_price' => 27000,
                'tax_id' => null,
                'category_id' => $catSpices ? $catSpices->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Bawang merah kering pilihan',
                'initial_stock' => 80, // 80 Kg
            ],
            [
                'name' => 'Bawang Putih Kating',
                'sku' => 'MBG-BWP-001',
                'sale_price' => 36000,
                'purchase_price' => 31000,
                'tax_id' => null,
                'category_id' => $catSpices ? $catSpices->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Bawang putih wangi',
                'initial_stock' => 60, // 60 Kg
            ],
            [
                'name' => 'Minyak Goreng Sawit 1L',
                'sku' => 'MBG-MYK-001',
                'sale_price' => 16500,
                'purchase_price' => 14800,
                'tax_id' => null,
                'category_id' => $catStaple ? $catStaple->id : 0,
                'unit_id' => $units['Liter'],
                'type' => 'product',
                'description' => 'Minyak goreng kemasan',
                'initial_stock' => 200, // 200 Liter
            ],
            [
                'name' => 'Tempe Daun Kedelai',
                'sku' => 'MBG-TMP-001',
                'sale_price' => 6000,
                'purchase_price' => 4800,
                'tax_id' => null,
                'category_id' => $catProtein ? $catProtein->id : 0,
                'unit_id' => $units['Pcs / Buah'],
                'type' => 'product',
                'description' => 'Tempe segar pengrajin lokal',
                'initial_stock' => 120, // 120 Pcs
            ],
            [
                'name' => 'Pisang Cavendish Manis',
                'sku' => 'MBG-PSG-001',
                'sale_price' => 18000,
                'purchase_price' => 15000,
                'tax_id' => null,
                'category_id' => $catVeg ? $catVeg->id : 0,
                'unit_id' => $units['Kg'],
                'type' => 'product',
                'description' => 'Buah penutup MBG',
                'initial_stock' => 180, // 180 Kg
            ],
        ];

        foreach ($products as $pData) {
            $initialStock = $pData['initial_stock'];
            unset($pData['initial_stock']);

            $p = ProductService::firstOrCreate(
                ['sku' => $pData['sku'], 'created_by' => $companyId, 'workspace_id' => $workspaceId],
                array_merge($pData, [
                    'quantity' => $initialStock,
                    'created_by' => $companyId,
                    'workspace_id' => $workspaceId,
                ])
            );

            // Set Initial Stock in Gudang Koperasi (Pusat) - warehouse_id: 1
            $whProd = WarehouseProduct::where('warehouse_id', 1)
                ->where('product_id', $p->id)
                ->first();

            if (!$whProd) {
                WarehouseProduct::create([
                    'warehouse_id' => 1,
                    'product_id' => $p->id,
                    'quantity' => $initialStock,
                    'workspace' => $workspaceId,
                    'created_by' => $companyId,
                ]);
            } else {
                $whProd->quantity = $initialStock;
                $whProd->save();
            }
        }
    }
}
