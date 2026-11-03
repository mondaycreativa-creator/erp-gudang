<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workspace',
        'created_by',
    ];


    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\ChartOfAccountTypeFactory::new();
    }

    public function scopeForCompanyOrGlobal($query)
    {
        $creator = creatorId();
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($creator, $workspace, $saId) {
            $q->where(function ($sub) use ($creator, $workspace) {
                $sub->where('created_by', $creator)
                    ->where('workspace', $workspace);
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
            $q->where('workspace', $workspace)
              ->orWhere('created_by', $saId)
              ->orWhere('created_by', 1);
        });
    }
}
