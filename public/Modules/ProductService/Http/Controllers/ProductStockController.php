<?php

namespace Modules\ProductService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ProductService\Entities\ProductService;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;

class ProductStockController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('product&service manage'))
        {

            $productServices = ProductService::where(function($q) {
                $saId = function_exists('superAdminId') ? superAdminId() : 1;
                $q->where(function($sub) {
                    $sub->where('created_by', '=', creatorId())
                        ->where('workspace_id', '=', getActiveWorkSpace());
                })
                ->orWhere('created_by', $saId)
                ->orWhere('created_by', 1);
            })->where('type', '=', 'product')->get();

            // Sinkronisasi dengan stok fisik Gudang Pusat
            $workspace = getActiveWorkSpace();
            $pusatWarehouse = function_exists('getPusatWarehouse')
                ? getPusatWarehouse($workspace)
                : (Warehouse::where('workspace', $workspace)
                    ->where('name', 'like', '%Pusat%')
                    ->orderBy('id', 'asc')
                    ->first()
                    ?? Warehouse::where('workspace', $workspace)
                        ->where('name', 'like', '%Koperasi%')
                        ->orderBy('id', 'asc')
                        ->first()
                    ?? Warehouse::where('workspace', $workspace)->orderBy('id', 'asc')->first());

            if ($pusatWarehouse) {
                $pusatStocks = WarehouseProduct::where('warehouse_id', $pusatWarehouse->id)
                    ->orderBy('updated_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('quantity', 'product_id')
                    ->toArray();

                $warehouseProductIds = WarehouseProduct::where('workspace', $workspace)
                    ->distinct()
                    ->pluck('product_id')
                    ->toArray();

                foreach ($productServices as $ps) {
                    if (in_array($ps->id, $warehouseProductIds)) {
                        $actualQty = isset($pusatStocks[$ps->id]) ? (float) $pusatStocks[$ps->id] : 0;
                        if ((float) $ps->quantity != $actualQty) {
                            $ps->quantity = $actualQty;
                            ProductService::where('id', $ps->id)->update(['quantity' => $actualQty]);
                        }
                    }
                }
            }

            return view('productservice::productstock.index', compact('productServices'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('productservice::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('productservice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $productService = ProductService::find($id);
        if(\Auth::user()->isAbleTo('product&service edit'))
        {
            $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [creatorId()];
            if($productService && in_array($productService->created_by, $allowedIds))
            {
                return view('productservice::productstock.edit', compact('productService'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if(\Auth::user()->isAbleTo('product&service edit'))
        {
            $productService = ProductService::find($id);
            $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [creatorId()];

            if($productService && in_array($productService->created_by, $allowedIds))
            {
                $addQty = (float) $request->quantity;

                // Sinkronkan ke stok fisik Gudang Pusat
                $pusat = function_exists('getPusatWarehouse')
                    ? getPusatWarehouse(getActiveWorkSpace())
                    : (Warehouse::where('workspace', getActiveWorkSpace())
                        ->where('name', 'like', '%Pusat%')
                        ->orderBy('id', 'asc')
                        ->first()
                        ?? Warehouse::where('workspace', getActiveWorkSpace())
                            ->where('name', 'like', '%Koperasi%')
                            ->orderBy('id', 'asc')
                            ->first()
                        ?? Warehouse::where('workspace', getActiveWorkSpace())->orderBy('id', 'asc')->first());

                if ($pusat) {
                    $whProduct = Purchase::addWarehouseStock($productService->id, $addQty, $pusat->id);
                    $productService->quantity = $whProduct ? (float) $whProduct->quantity : max(0, (float) $productService->quantity + $addQty);
                } else {
                    $productService->quantity = max(0, (float) $productService->quantity + $addQty);
                }
                $productService->save();

                //Product Stock Report
                $type        = 'manually';
                $type_id     = 0;
                $description = $request->quantity . '  ' . __('quantity added by manually');
                if(module_is_active('Account'))
                {
                    ProductService::addProductStock($productService->id, $request->quantity, $type, $description, $type_id);
                }

                return redirect()->route('productstock.index')->with('success', __('Product quantity updated manually.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

}
