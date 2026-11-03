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
        $creator = creatorId();
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($creator, $workspace, $saId) {
            $q->where(function ($sub) use ($creator, $workspace) {
                $sub->where('chart_of_account_parents.created_by', $creator)
                    ->where('chart_of_account_parents.workspace', $workspace);
            })
            ->orWhere('chart_of_account_parents.created_by', $saId)
            ->orWhere('chart_of_account_parents.created_by', 1);
        });
    }

    public function scopeForWorkspaceOrGlobal($query)
    {
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($workspace, $saId) {
            $q->where('chart_of_account_parents.workspace', $workspace)
              ->orWhere('chart_of_account_parents.created_by', $saId)
              ->orWhere('chart_of_account_parents.created_by', 1);
        });
    }
}
