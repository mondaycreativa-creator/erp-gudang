<?php

namespace Modules\ProductService\Events;

use Illuminate\Queue\SerializesModels;

class CreateProduct
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $productService;
    public function __construct($arg1 = null, $arg2 = null)
    {
        if ($arg1 instanceof \Illuminate\Http\Request) {
            $this->request = $arg1;
            $this->productService = $arg2;
        } elseif ($arg2 instanceof \Illuminate\Http\Request) {
            $this->request = $arg2;
            $this->productService = $arg1;
        } else {
            $this->productService = $arg1;
            $this->request = $arg2;
        }
    }
}
