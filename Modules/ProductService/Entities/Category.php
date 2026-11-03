<?php

namespace Modules\ProductService\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Account\Entities\ChartOfAccount;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [];
    public static $categoryType = [
        'Product & Service',
        'Income',
        'Expense',
    ];
    protected static function newFactory()
    {
        return \Modules\ProductService\Database\factories\CategoryFactory::new();
    }


    public function chartAccount()
    {
        return $this->hasOne(ChartOfAccount::class, 'id', 'chart_account_id');
    }

    public function scopeForCompanyOrGlobal($query)
    {
        $creator = creatorId();
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($creator, $workspace, $saId) {
            $q->where(function ($sub) use ($creator, $workspace) {
                $sub->where('created_by', $creator)
                    ->where('workspace_id', $workspace);
            })
            ->orWhere('created_by', $saId)
            ->orWhere('created_by', 1);
        });
    }

    public function scopeForWorkspaceOrGlobal($query)
    {
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($workspace, $saId) {
            $q->where('workspace_id', $workspace)
              ->orWhere('created_by', $saId)
              ->orWhere('created_by', 1);
        });
    }

}

