<?php

namespace App\Http\Controllers;

use App\Models\MbgDispatch;
use App\Models\MbgDispatchItem;
use App\Models\MbgKitchenUsage;
use App\Models\MbgKitchenUsageItem;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ProductService\Entities\Category;
use Modules\ProductService\Entities\ProductService;

class MbgController extends Controller
{
    /**
     * Helper to get user's assigned warehouse if restricted (SPPG Admin)
     */
    private function getUserSppgWarehouseId()
    {
        $user = Auth::user();
        if ($user->type == 'Admin SPPG Pelutan' || $user->type == 'Asisten Lapangan SPPG Pelutan') {
            $wh = Warehouse::where('name', 'like', '%Pelutan%')->first();
            return $wh ? $wh->id : null;
        }
        if ($user->type == 'Admin SPPG Kerep' || $user->type == 'Asisten Lapangan SPPG Kerep') {
            $wh = Warehouse::where('name', 'like', '%Kerep%')->first();
            return $wh ? $wh->id : null;
        }
        return null;
    }

    // =========================================================================
    // 1. PENGIRIMAN MINGGUAN DENGAN HARI (WEEKLY DISPATCH / BULK TRANSFER)
    // =========================================================================

    public function dispatches(Request $request)
    {
        $workspace = getActiveWorkSpace();
        $query = MbgDispatch::where('workspace', $workspace)->with(['fromWarehouse', 'toWarehouse', 'items.product']);

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('delivery_day')) {
            $query->where('delivery_day', $request->delivery_day);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('delivery_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $dispatches = $query->orderBy('delivery_date', 'desc')->get();
        $warehouses = Warehouse::where('workspace', $workspace)->get();

        return view('mbg.dispatches.index', compact('dispatches', 'warehouses'));
    }

    public function createDispatch()
    {
        $workspace = getActiveWorkSpace();
        $koperasiWarehouse = Warehouse::where('workspace', $workspace)->where('name', 'like', '%Koperasi%')->first()
            ?? Warehouse::where('workspace', $workspace)->first();

        $sppgWarehouses = Warehouse::where('workspace', $workspace)
            ->where('id', '!=', $koperasiWarehouse ? $koperasiWarehouse->id : 0)
            ->get();

        $products = ProductService::where('workspace_id', $workspace)
            ->with(['unit', 'category'])
            ->get();

        $koperasiStocks = [];
        if ($koperasiWarehouse) {
            $koperasiStocks = WarehouseProduct::where('warehouse_id', $koperasiWarehouse->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('mbg.dispatches.create', compact('koperasiWarehouse', 'sppgWarehouses', 'products', 'koperasiStocks', 'days'));
    }

    public function storeDispatch(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'delivery_day' => 'required|string',
            'delivery_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:product_services,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $workspace = getActiveWorkSpace();
            $creator = creatorId();

            $dateStr = date('Ymd', strtotime($request->delivery_date));
            $dispatchCount = MbgDispatch::whereDate('created_at', today())->count() + 1;
            $dispatchCode = 'MBG-KRM-' . $dateStr . '-' . str_pad($dispatchCount, 3, '0', STR_PAD_LEFT);

            $totalPrice = 0;
            $totalCost = 0;
            $cashbackPercent = $request->cashback_percent ?? 0;

            // Prepare items and calculate totals
            $itemsData = [];
            foreach ($request->products as $item) {
                if (empty($item['quantity']) || $item['quantity'] <= 0) {
                    continue;
                }
                $product = ProductService::find($item['product_id']);
                if (!$product) continue;

                $qty = (float) $item['quantity'];
                $purchasePrice = (float) $product->purchase_price;
                $salePrice = (float) $product->sale_price;

                $subtotalCost = $qty * $purchasePrice;
                $subtotalPrice = $qty * $salePrice;

                $totalCost += $subtotalCost;
                $totalPrice += $subtotalPrice;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                    'subtotal_cost' => $subtotalCost,
                    'subtotal_price' => $subtotalPrice,
                    'notes' => $item['notes'] ?? null,
                ];

                // Perform real-time stock transfer in ERPGo
                WarehouseTransfer::warehouse_transfer_qty(
                    $request->from_warehouse_id,
                    $request->to_warehouse_id,
                    $product->id,
                    $qty
                );

                // Create standard ERPGo WarehouseTransfer record for native audit log
                WarehouseTransfer::create([
                    'from_warehouse' => $request->from_warehouse_id,
                    'to_warehouse' => $request->to_warehouse_id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'date' => $request->delivery_date,
                    'workspace' => $workspace,
                    'created_by' => $creator,
                ]);
            }

            if (empty($itemsData)) {
                return redirect()->back()->with('error', 'Silakan masukkan minimal 1 produk dengan jumlah yang valid.');
            }

            $cashbackAmount = ($totalPrice * $cashbackPercent) / 100;

            $dispatch = MbgDispatch::create([
                'dispatch_code' => $dispatchCode,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'delivery_day' => $request->delivery_day,
                'delivery_date' => $request->delivery_date,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'total_price' => $totalPrice,
                'total_cost' => $totalCost,
                'cashback_percent' => $cashbackPercent,
                'cashback_amount' => $cashbackAmount,
                'paid_amount' => 0,
                'notes' => $request->notes,
                'workspace' => $workspace,
                'created_by' => $creator,
            ]);

            foreach ($itemsData as $it) {
                $dispatch->items()->create($it);
            }

            DB::commit();
            return redirect()->route('mbg.dispatches.show', $dispatch->id)
                ->with('success', 'Pengiriman stok mingguan berhasil disimpan dan stok gudang telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showDispatch($id)
    {
        $dispatch = MbgDispatch::with(['fromWarehouse', 'toWarehouse', 'items.product.unit'])->findOrFail($id);
        return view('mbg.dispatches.show', compact('dispatch'));
    }

    public function destroyDispatch($id)
    {
        $dispatch = MbgDispatch::with('items')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Revert stock transfer
            foreach ($dispatch->items as $item) {
                WarehouseTransfer::warehouse_transfer_qty(
                    $dispatch->to_warehouse_id,
                    $dispatch->from_warehouse_id,
                    $item->product_id,
                    $item->quantity
                );
            }
            $dispatch->delete();
            DB::commit();
            return redirect()->route('mbg.dispatches.index')->with('success', 'Data pengiriman berhasil dihapus dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. PEMAKAIAN DAPUR SPPG & BARANG TAMBAHAN
    // =========================================================================

    public function usages(Request $request)
    {
        $workspace = getActiveWorkSpace();
        $userWhId = $this->getUserSppgWarehouseId();

        $query = MbgKitchenUsage::where('workspace', $workspace)
            ->with(['warehouse', 'items.product.unit']);

        if ($userWhId) {
            $query->where('warehouse_id', $userWhId);
        } elseif ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('usage_date', [$request->start_date, $request->end_date]);
        }

        $usages = $query->orderBy('usage_date', 'desc')->get();
        $warehouses = Warehouse::where('workspace', $workspace)
            ->where('name', 'like', '%SPPG%')
            ->get();

        return view('mbg.usages.index', compact('usages', 'warehouses', 'userWhId'));
    }

    public function createUsage()
    {
        $workspace = getActiveWorkSpace();
        $userWhId = $this->getUserSppgWarehouseId();

        if ($userWhId) {
            $sppgWarehouses = Warehouse::where('id', $userWhId)->get();
        } else {
            $sppgWarehouses = Warehouse::where('workspace', $workspace)
                ->where('name', 'like', '%SPPG%')
                ->get();
        }

        $products = ProductService::where('workspace_id', $workspace)
            ->with(['unit', 'category'])
            ->get();

        // Get stocks per warehouse
        $warehouseStocks = [];
        foreach ($sppgWarehouses as $wh) {
            $warehouseStocks[$wh->id] = WarehouseProduct::where('warehouse_id', $wh->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
        }

        return view('mbg.usages.create', compact('sppgWarehouses', 'products', 'warehouseStocks', 'userWhId'));
    }

    public function storeUsage(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'usage_date' => 'required|date',
            'meal_session' => 'required|string',
            'portion_count' => 'required|numeric|min:1',
            'standard_products' => 'nullable|array',
            'additional_products' => 'nullable|array',
        ]);

        if (empty($request->standard_products) && empty($request->additional_products)) {
            return redirect()->back()->with('error', 'Harap masukkan minimal 1 bahan terpakai atau bahan tambahan.');
        }

        DB::beginTransaction();
        try {
            $workspace = getActiveWorkSpace();
            $creator = creatorId();

            $dateStr = date('Ymd', strtotime($request->usage_date));
            $usageCount = MbgKitchenUsage::whereDate('created_at', today())->count() + 1;
            $usageCode = 'MBG-MSK-' . $dateStr . '-' . str_pad($usageCount, 3, '0', STR_PAD_LEFT);

            $usage = MbgKitchenUsage::create([
                'usage_code' => $usageCode,
                'warehouse_id' => $request->warehouse_id,
                'usage_date' => $request->usage_date,
                'meal_session' => $request->meal_session,
                'portion_count' => $request->portion_count,
                'menu_name' => $request->menu_name,
                'notes' => $request->notes,
                'workspace' => $workspace,
                'created_by' => $creator,
            ]);

            // 1. Process Standard Used Items (Deduct from SPPG Warehouse stock)
            if (!empty($request->standard_products)) {
                foreach ($request->standard_products as $item) {
                    if (empty($item['quantity']) || $item['quantity'] <= 0) continue;

                    $qty = (float) $item['quantity'];
                    $usage->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $qty,
                        'type' => 'standard',
                        'additional_cost' => 0,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    // Deduct stock in SPPG warehouse
                    $whProduct = WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if ($whProduct) {
                        $whProduct->quantity = max(0, $whProduct->quantity - $qty);
                        $whProduct->save();
                    }
                }
            }

            // 2. Process Additional / Emergency Items
            if (!empty($request->additional_products)) {
                foreach ($request->additional_products as $item) {
                    if (empty($item['quantity']) || $item['quantity'] <= 0) continue;

                    $qty = (float) $item['quantity'];
                    $cost = isset($item['additional_cost']) ? (float) $item['additional_cost'] : 0;

                    $usage->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $qty,
                        'type' => 'additional',
                        'additional_cost' => $cost,
                        'notes' => $item['notes'] ?? 'Bahan tambahan darurat masak',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mbg.usages.show', $usage->id)
                ->with('success', 'Pencatatan pemakaian dapur SPPG berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showUsage($id)
    {
        $usage = MbgKitchenUsage::with(['warehouse', 'items.product.unit'])->findOrFail($id);
        return view('mbg.usages.show', compact('usage'));
    }

    // =========================================================================
    // 3. REKAP TAGIHAN DAPUR, MARGIN LABA & CASHBACK KOPERASI
    // =========================================================================

    public function billing(Request $request)
    {
        $workspace = getActiveWorkSpace();
        $query = MbgDispatch::where('workspace', $workspace)
            ->with(['fromWarehouse', 'toWarehouse', 'items.product']);

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('delivery_date', [$request->start_date, $request->end_date]);
        }

        $dispatches = $query->orderBy('delivery_date', 'desc')->get();

        // Calculate KPI summary
        $totalBilling = $dispatches->sum('total_price');
        $totalCost = $dispatches->sum('total_cost');
        $grossProfit = $totalBilling - $totalCost;
        $totalCashback = $dispatches->sum('cashback_amount');
        $netProfit = $grossProfit - $totalCashback;
        $totalPaid = $dispatches->sum('paid_amount');
        $totalUnpaid = $dispatches->sum(function ($d) {
            return $d->unpaid_amount;
        });

        $warehouses = Warehouse::where('workspace', $workspace)
            ->where('name', 'like', '%SPPG%')
            ->get();

        return view('mbg.billing.index', compact(
            'dispatches', 'warehouses', 'totalBilling', 'totalCost',
            'grossProfit', 'totalCashback', 'netProfit', 'totalPaid', 'totalUnpaid'
        ));
    }

    public function updatePayment(Request $request, $id)
    {
        $dispatch = MbgDispatch::findOrFail($id);
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        if ($request->payment_status == 'paid') {
            $dispatch->paid_amount = $dispatch->total_price;
        } elseif ($request->payment_status == 'unpaid') {
            $dispatch->paid_amount = 0;
        } else {
            $dispatch->paid_amount = (float) $request->paid_amount;
        }

        $dispatch->payment_status = $request->payment_status;
        $dispatch->save();

        return redirect()->back()->with('success', 'Status pembayaran tagihan dapur berhasil diperbarui.');
    }

    public function updateCashback(Request $request, $id)
    {
        $dispatch = MbgDispatch::findOrFail($id);
        $request->validate([
            'cashback_percent' => 'nullable|numeric|min:0|max:100',
            'cashback_amount' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('cashback_percent')) {
            $percent = (float) $request->cashback_percent;
            $dispatch->cashback_percent = $percent;
            $dispatch->cashback_amount = ($dispatch->total_price * $percent) / 100;
        } elseif ($request->filled('cashback_amount')) {
            $amount = (float) $request->cashback_amount;
            $dispatch->cashback_amount = $amount;
            $dispatch->cashback_percent = $dispatch->total_price > 0 ? ($amount / $dispatch->total_price) * 100 : 0;
        }

        $dispatch->save();
        return redirect()->back()->with('success', 'Data cashback koperasi berhasil disimpan.');
    }

    // =========================================================================
    // 4. LAPORAN DEADSTOCK MBG (SLOW MOVING & STOK MENGENDAP)
    // =========================================================================

    public function deadstock(Request $request)
    {
        $workspace = getActiveWorkSpace();
        $warehouses = Warehouse::where('workspace', $workspace)->get();
        $selectedWarehouseId = $request->warehouse_id ?? ($warehouses->first() ? $warehouses->first()->id : 1);

        $thresholdWarning = (int) ($request->threshold_warning ?? 3); // 3 hari
        $thresholdCritical = (int) ($request->threshold_critical ?? 7); // 7 hari

        // Query products with stock in the selected warehouse
        $stockRecords = WarehouseProduct::where('warehouse_id', $selectedWarehouseId)
            ->where('quantity', '>', 0)
            ->with(['product.unit', 'product.category'])
            ->get();

        $deadstockList = [];
        $totalDeadstockValue = 0;
        $totalCriticalValue = 0;
        $criticalCount = 0;
        $warningCount = 0;
        $freshCount = 0;

        $now = now();

        foreach ($stockRecords as $record) {
            $product = $record->product;
            if (!$product) continue;

            // Find last outgoing activity for this product in this warehouse
            // 1. From MbgDispatch
            $lastDispatchDate = MbgDispatchItem::join('mbg_dispatches', 'mbg_dispatch_items.dispatch_id', '=', 'mbg_dispatches.id')
                ->where('mbg_dispatches.from_warehouse_id', $selectedWarehouseId)
                ->where('mbg_dispatch_items.product_id', $product->id)
                ->max('mbg_dispatches.delivery_date');

            // 2. From MbgKitchenUsage
            $lastUsageDate = MbgKitchenUsageItem::join('mbg_kitchen_usages', 'mbg_kitchen_usage_items.usage_id', '=', 'mbg_kitchen_usages.id')
                ->where('mbg_kitchen_usages.warehouse_id', $selectedWarehouseId)
                ->where('mbg_kitchen_usage_items.product_id', $product->id)
                ->max('mbg_kitchen_usages.usage_date');

            // 3. Fallback to warehouse product updated_at
            $lastDates = array_filter([$lastDispatchDate, $lastUsageDate]);
            if (!empty($lastDates)) {
                $lastMovement = max($lastDates);
            } else {
                $lastMovement = $record->updated_at ? $record->updated_at->format('Y-m-d') : $record->created_at->format('Y-m-d');
            }

            $diffDays = (int) $now->diffInDays(\Carbon\Carbon::parse($lastMovement));

            $assetValue = $record->quantity * ($product->purchase_price ?? 0);
            $totalDeadstockValue += $assetValue;

            if ($diffDays >= $thresholdCritical) {
                $status = 'critical'; // Deadstock Kritis (> 7 hari)
                $statusText = 'DEADSTOCK / Kritis';
                $badgeClass = 'bg-danger';
                $criticalCount++;
                $totalCriticalValue += $assetValue;
            } elseif ($diffDays >= $thresholdWarning) {
                $status = 'warning'; // Slow moving (3-7 hari)
                $statusText = 'Slow Moving / Perhatian';
                $badgeClass = 'bg-warning text-dark';
                $warningCount++;
            } else {
                $status = 'fresh'; // Lancar (< 3 hari)
                $statusText = 'Lancar / Segar';
                $badgeClass = 'bg-success';
                $freshCount++;
            }

            $deadstockList[] = (object) [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category ? $product->category->name : '-',
                'unit' => $product->unit ? $product->unit->name : 'Satuan',
                'quantity' => $record->quantity,
                'purchase_price' => $product->purchase_price,
                'asset_value' => $assetValue,
                'last_movement' => $lastMovement,
                'days_stagnant' => $diffDays,
                'status' => $status,
                'status_text' => $statusText,
                'badge_class' => $badgeClass,
            ];
        }

        // Sort by stagnant days descending
        usort($deadstockList, function ($a, $b) {
            return $b->days_stagnant <=> $a->days_stagnant;
        });

        $currentWarehouse = Warehouse::find($selectedWarehouseId);

        return view('mbg.deadstock.index', compact(
            'deadstockList', 'warehouses', 'selectedWarehouseId', 'currentWarehouse',
            'thresholdWarning', 'thresholdCritical', 'totalDeadstockValue',
            'totalCriticalValue', 'criticalCount', 'warningCount', 'freshCount'
        ));
    }

    // =========================================================================
    // 5. ASISTEN LAPANGAN: VIEW STOK KOPERASI & QUICK PICKUP
    // =========================================================================

    public function koperasiStock(Request $request)
    {
        $workspace = getActiveWorkSpace();
        $koperasiWarehouse = Warehouse::where('workspace', $workspace)
            ->where('name', 'like', '%Koperasi%')
            ->first() ?? Warehouse::where('workspace', $workspace)->first();

        $stockQuery = WarehouseProduct::where('warehouse_id', $koperasiWarehouse ? $koperasiWarehouse->id : 1)
            ->with(['product.unit', 'product.category']);

        if ($request->filled('category_id')) {
            $stockQuery->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $stockQuery->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $stocks = $stockQuery->get();
        $categories = Category::where('workspace_id', $workspace)->where('type', 0)->get();

        return view('mbg.lapangan.stock', compact('stocks', 'koperasiWarehouse', 'categories'));
    }
}
