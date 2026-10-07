<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\ProductService\Entities\ProductService;

class MbgDispatchItem extends Model
{
    use HasFactory;

    protected $table = 'mbg_dispatch_items';

    protected $fillable = [
        'dispatch_id',
        'product_id',
        'quantity',
        'purchase_price',
        'sale_price',
        'subtotal_cost',
        'subtotal_price',
        'notes',
    ];

    public function dispatch()
    {
        return $this->belongsTo(MbgDispatch::class, 'dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }
}
