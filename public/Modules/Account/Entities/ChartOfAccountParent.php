<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccountParent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_type',
        'type',
        'parent',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\ChartOfAccountParentFactory::new();
    }

    public function scopeForCompanyOrGlobal($query)
    {
        $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [0, 1, 2];
        return $query->whereIn('chart_of_account_parents.created_by', $allowedIds);
    }

    public function scopeForWorkspaceOrGlobal($query)
    {
        $allowedIds = function_exists('globalAdminUserIds') ? globalAdminUserIds() : [0, 1, 2];
        return $query->whereIn('chart_of_account_parents.created_by', $allowedIds);
    }
}
