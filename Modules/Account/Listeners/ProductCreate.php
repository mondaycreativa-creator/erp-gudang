<?php

namespace Modules\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\ProductService\Entities\ProductService;
use Modules\ProductService\Events\CreateProduct;

class ProductCreate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreateProduct $event)
    {
        $request = ($event->request instanceof \Illuminate\Http\Request) ? $event->request : (($event->productService instanceof \Illuminate\Http\Request) ? $event->productService : null);
        $productModel = ($event->productService instanceof ProductService) ? $event->productService : (($event->request instanceof ProductService) ? $event->request : null);

        if (!$productModel && $request && !empty($request->id)) {
            $productModel = ProductService::find($request->id);
        }

        if ($productModel) {
            $saleId = ($request && !empty($request->sale_chartaccount_id)) ? (int)$request->sale_chartaccount_id : (!empty($productModel->sale_chartaccount_id) ? (int)$productModel->sale_chartaccount_id : 0);
            $expenseId = ($request && !empty($request->expense_chartaccount_id)) ? (int)$request->expense_chartaccount_id : (!empty($productModel->expense_chartaccount_id) ? (int)$productModel->expense_chartaccount_id : 0);

            ProductService::where('id', $productModel->id)->update([
                'sale_chartaccount_id' => $saleId,
                'expense_chartaccount_id' => $expenseId,
            ]);
        }
    }
}
