<?php

namespace Modules\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\ProductService\Entities\ProductService;
use Modules\ProductService\Events\UpdateProduct;

class ProductUpdate
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
    public function handle(UpdateProduct $event)
    {
        $request = ($event->request instanceof \Illuminate\Http\Request) ? $event->request : (($event->productService instanceof \Illuminate\Http\Request) ? $event->productService : null);
        $productModel = ($event->productService instanceof ProductService) ? $event->productService : (($event->request instanceof ProductService) ? $event->request : null);

        if (!$productModel && $request && !empty($request->id)) {
            $productModel = ProductService::find($request->id);
        }

        if ($productModel) {
            $updateData = [];
            if ($request && isset($request->sale_chartaccount_id)) {
                $updateData['sale_chartaccount_id'] = !empty($request->sale_chartaccount_id) ? (int)$request->sale_chartaccount_id : 0;
            }
            if ($request && isset($request->expense_chartaccount_id)) {
                $updateData['expense_chartaccount_id'] = !empty($request->expense_chartaccount_id) ? (int)$request->expense_chartaccount_id : 0;
            }
            if (!empty($updateData)) {
                ProductService::where('id', $productModel->id)->update($updateData);
            }
        }
    }
}
