<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MbgDispatch extends Model
{
    use HasFactory;

    protected $table = 'mbg_dispatches';

    protected $fillable = [
        'dispatch_code',
        'from_warehouse_id',
        'to_warehouse_id',
        'delivery_day',
        'delivery_date',
        'status',
        'payment_status',
        'total_price',
        'total_cost',
        'cashback_percent',
        'cashback_amount',
        'paid_amount',
        'notes',
        'workspace',
        'created_by',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(MbgDispatchItem::class, 'dispatch_id');
    }

    public function getGrossProfitAttribute()
    {
        return $this->total_price - $this->total_cost;
    }

    public function getNetProfitAttribute()
    {
        return $this->gross_profit - $this->cashback_amount;
    }

    public function getUnpaidAmountAttribute()
    {
        return max(0, $this->total_price - $this->paid_amount);
    }
}
