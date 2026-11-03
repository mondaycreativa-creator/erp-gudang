<?php

namespace Modules\ProductService\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\ProductService\Database\factories\UnitFactory::new();
    }

    public function scopeForCompanyOrGlobal($query)
    {
        $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [0, 1, 2];
        return $query->whereIn('created_by', $allowedIds);
    }

    public function scopeForWorkspaceOrGlobal($query)
    {
        $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [0, 1, 2];
        return $query->whereIn('created_by', $allowedIds);
    }
}
