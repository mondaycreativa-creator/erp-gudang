<?php

namespace Modules\Account\Entities;

use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'sub_type',
        'parent',
        'is_enabled',
        'description',
        'workspace',
        'created_by',
    ];



    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\ChartOfAccountFactory::new();
    }

    public function types()
    {
        return $this->hasOne('Modules\Account\Entities\ChartOfAccountType', 'id', 'type');
    }

    public function scopeForCompanyOrGlobal($query)
    {
        $creator = creatorId();
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($creator, $workspace, $saId) {
            $q->where(function ($sub) use ($creator, $workspace) {
                $sub->where('chart_of_accounts.created_by', $creator)
                    ->where('chart_of_accounts.workspace', $workspace);
            })
            ->orWhere('chart_of_accounts.created_by', $saId)
            ->orWhere('chart_of_accounts.created_by', 1);
        });
    }

    public function scopeForWorkspaceOrGlobal($query)
    {
        $workspace = getActiveWorkSpace();
        $saId = function_exists('superAdminId') ? superAdminId() : 1;

        return $query->where(function ($q) use ($workspace, $saId) {
            $q->where('chart_of_accounts.workspace', $workspace)
              ->orWhere('chart_of_accounts.created_by', $saId)
              ->orWhere('chart_of_accounts.created_by', 1);
        });
    }

    // public function subType()
    // {
    //     return $this->hasOne('Modules\Account\Entities\ChartOfAccountSubType', 'id', 'sub_type');
    // }

//    public function balance()
//    {
//        $journalItem         = JournalItem::select(\DB::raw('sum(credit) as totalCredit'),
//            \DB::raw('sum(debit) as totalDebit'),
//            \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $this->id);
//        $journalItem         = $journalItem->first();
//        $data['totalCredit'] = $journalItem->totalCredit;
//        $data['totalDebit']  = $journalItem->totalDebit;
//        $data['netAmount']   = $journalItem->netAmount;
//
//        return $data;
//    }

    public function subType()
    {
        return $this->belongsTo(ChartOfAccountSubType::class, 'sub_type');
    }
    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccountParent::class, 'parent');
    }


    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'chart_account_id');
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class, 'account_id');
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class, 'account_id');
    }

    public function billAccounts()
    {
        return $this->hasMany(BillAccount::class, 'chart_account_id');
    }

    public function billPayments()
    {
        return $this->hasMany(BillPayment::class, 'account_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'account_id');
    }

    // public function journalItems()
    // {
    //     return $this->hasMany(JournalItem::class, 'account');
    // }


}
