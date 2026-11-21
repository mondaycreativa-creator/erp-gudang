<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\ProductService\Entities\ProductService;

class MbgKitchenUsageItem extends Model
{
    use HasFactory;

    protected $table = 'mbg_kitchen_usage_items';

    protected $fillable = [
        'usage_id',
        'product_id',
        'product_name',
        'unit_name',
        'quantity',
        'type', // 'standard' or 'additional'
        'additional_cost',
        'notes',
    ];

    public function getDisplayNameAttribute()
    {
        if ($this->product) {
            return $this->product->name;
        }
        return $this->product_name ?: '-';
    }

    public function getDisplayUnitAttribute()
    {
        if ($this->product && $this->product->unit) {
            return $this->product->unit->name;
        }
        return $this->unit_name ?: 'Satuan';
    }

    public function usage()
    {
        return $this->belongsTo(MbgKitchenUsage::class, 'usage_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }
}
