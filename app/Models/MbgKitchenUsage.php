<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MbgKitchenUsage extends Model
{
    use HasFactory;

    protected $table = 'mbg_kitchen_usages';

    protected $fillable = [
        'usage_code',
        'warehouse_id',
        'usage_date',
        'meal_session',
        'portion_count',
        'menu_name',
        'notes',
        'workspace',
        'created_by',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(MbgKitchenUsageItem::class, 'usage_id');
    }

    public function standardItems()
    {
        return $this->hasMany(MbgKitchenUsageItem::class, 'usage_id')->where('type', 'standard');
    }

    public function additionalItems()
    {
        return $this->hasMany(MbgKitchenUsageItem::class, 'usage_id')->where('type', 'additional');
    }
}
