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
