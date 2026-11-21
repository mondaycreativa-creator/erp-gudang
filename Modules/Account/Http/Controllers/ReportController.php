<?php

namespace Modules\Account\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Purchase;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\AccountUtility;
use Modules\Account\Entities\BankAccount;
use Modules\Account\Entities\Bill;
use Modules\Account\Entities\BillProduct;
use Modules\Account\Entities\Customer;
use Modules\Account\Entities\Payment;
use Modules\Account\Entities\Revenue;
use Modules\Account\Entities\StockReport;
use Modules\Account\Entities\Vender;
use Modules\Hrm\Entities\PaySlip;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function accountStatement(Request $request)
    {
        if(Auth::user()->isAbleTo('report statement manage'))
        {

            $filter['account']             = __('All');
            $filter['type']                = __('Revenue');
            $reportData['revenues']        = '';
            $reportData['payments']        = '';
            $reportData['revenueAccounts'] = '';
            $reportData['paymentAccounts'] = '';

            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $types = [
                'revenue' => __('Revenue'),
                'payment' => __('Payment'),
            ];

            if(!isset($request->type))
            {
                $request->type = 'payment';
            }

            if($request->type == 'revenue' || !isset($request->type))
            {

                $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', creatorId());

                $revenues = Revenue::where('revenues.created_by', '=', creatorId())->orderBy('id', 'desc');
            }

            if($request->type == 'payment')
            {
                $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.workspace', '=', getActiveWorkSpace());
                $payments = Payment::where('payments.workspace', '=', getActiveWorkSpace())->orderBy('id', 'desc');
            }


            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }
            $currentdate = $start;
            while($currentdate <= $end)
            {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);

                if($request->type == 'payment')
                {
                    $paymentAccounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('payments.workspace', '=',getActiveWorkSpace());
                        }
                    );
                }
                if($request->type == 'revenue')
                {
                    $revenueAccounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.workspace', '=',getActiveWorkSpace());
                        }
                    );
                }


                $currentdate = strtotime('+1 month', $currentdate);
            }
            if(!empty($request->account))
            {
                if($request->type == 'revenue' )
                {
                    $revenues->where('account_id', $request->account);
                    $revenues->where('revenues.workspace', '=', getActiveWorkSpace());
                    $revenueAccounts->where('account_id', $request->account);
                    $revenueAccounts->where('revenues.workspace', '=', getActiveWorkSpace());
                }

                if($request->type == 'payment')
                {
                    $payments->where('account_id', $request->account);
                    $payments->where('payments.workspace', '=',getActiveWorkSpace());

                    $paymentAccounts->where('account_id', $request->account);
                    $paymentAccounts->where('payments.workspace', '=',getActiveWorkSpace());
                }


                $bankAccount       = BankAccount::find($request->account);
                $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                if($bankAccount->holder_name == 'Cash')
                {
                    $filter['account'] = 'Cash';
                }
            }

            if($request->type == 'revenue' )
            {
                $reportData['revenues'] = $revenues->get();

                $revenueAccounts->where('revenues.workspace', '=',getActiveWorkSpace());
                $reportData['revenueAccounts'] = $revenueAccounts->get();
                $filter['type']                = __('Revenue');
            }

            if($request->type == 'payment')
            {
                $reportData['payments'] = $payments->get();

                $paymentAccounts->where('payments.workspace', '=',getActiveWorkSpace());
                $reportData['paymentAccounts'] = $paymentAccounts->get();
                $filter['type']                = __('Payment');
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('account::report.statement_report', compact('reportData', 'account', 'types', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function incomeSummary(Request $request)
    {

            if(\Auth::user()->isAbleTo('report income manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

                $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category = \Modules\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 1)->get()->pluck('name', 'id');
                }

                $data['monthList']  = $month = $this->yearMonth();
                $data['yearList']   = $this->yearList();
                $filter['category'] = __('All');
                $filter['customer'] = __('All');

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // ------------------------------REVENUE INCOME-----------------------------------
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'revenues.category_id', '=', 'categories.id')->where('categories.type', '=', 1);
                $incomes->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                if(!empty($request->category))
                {
                    $incomes->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                = \Modules\ProductService\Entities\Category::find($request->category);
                    }
                        $filter['category'] = !empty($cat) ? $cat->name : '';
                }

                if(!empty($request->customer))
                {
                    $incomes->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes = $incomes->get();

                $tmpArray = [];
                foreach($incomes as $income)
                {
                    $tmpArray[$income->category_id][$income->month] = $income->amount;
                }
                $array = [];
                foreach($tmpArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $tmp['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
                    $array[] = $tmp;
                }


                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();

                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                }

                //---------------------------INVOICE INCOME-----------------------------------------------

                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('user_id', '=', $cust->user_id);
                }

                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }

                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {
                    $invoice             = [];
                    $invoice['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoice['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type','1')->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type','1')->first()->id : '';
                    }
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }

                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
                }

                $chartIncomeArr = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $incomeTotal, $invoiceTotal
                );
                $data['chartIncomeArr'] = $chartIncomeArr;
                $data['incomeArr']      = $array;
                $data['invoiceArray']   = $invoiceArray;
                $data['account']        = $account;
                $data['customer']       = $customer;
                $data['category']       = $category;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
                return view('account::report.income_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function expenseSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report expense manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

                $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category = \Modules\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 2)->get()->pluck('name', 'id');
                }

                $data['monthList']  = $month = $this->yearMonth();
                $data['yearList']   = $this->yearList();
                $filter['category'] = __('All');
                $filter['vendor']   = __('All');

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'payments.category_id', '=', 'categories.id')->where('categories.type', '=', 2);
                $expenses->where('payments.workspace', '=', getActiveWorkSpace());
                $expenses->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expenses->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                = \Modules\ProductService\Entities\Category::find($request->category);
                    }
                    $filter['category'] = !empty($cat) ? $cat->name : '';
                }
                if(!empty($request->vendor))
                {
                    $expenses->where('vendor_id', '=', $request->vendor);

                    $vend             = Vender::find($request->vendor);
                    $filter['vendor'] = !empty($vend) ? $vend->name : '';
                }

                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();
                $tmpArray = [];
                foreach($expenses as $expense)
                {
                    $tmpArray[$expense->category_id][$expense->month] = $expense->amount;
                }
                $array = [];
                foreach($tmpArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $tmp['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
                    $array[] = $tmp;
                }
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
                $expensesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->vendor))
                {
                    $expensesData->where('vendor_id', '=', $request->vendor);
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();

                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                }

                //     ------------------------------------BILL EXPENSE----------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace',getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);

                if(!empty($request->vendor))
                {
                    $bills->where('vendor_id', '=', $request->vendor);
                }

                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }

                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {

                    $bill             = [];
                    $bill['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $bill['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                }

            //     ------------------------------------Purchase EXPENSE----------------------------------------------------

            $purchases = Purchase::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,purchase_id,id')->where('workspace',getActiveWorkSpace())->where('status', '!=', 0);
            $purchases->whereRAW('YEAR(send_date) =?', [$year]);

            if(!empty($request->vendor))
            {
                $purchases->where('vendor_id', '=', $request->vendor);
            }

            if(!empty($request->category))
            {
                $purchases->where('category_id', '=', $request->category);
            }
            $purchases        = $purchases->get();
            $purchaseTmpArray = [];
            foreach($purchases as $purchase)
            {
                $purchaseTmpArray[$purchase->category_id][$purchase->month][] = $purchase->getTotal();
            }

            $purchaseArray = [];
            foreach($purchaseTmpArray as $cat_id => $record)
            {

                $purchase             = [];
                $purchase['category'] = [];
                if(module_is_active('ProductService'))
                {
                    $purchase['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $purchase['data']     = [];
                for($i = 1; $i <= 12; $i++)
                {

                    $purchase['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $purchaseArray[] = $purchase;
            }
            $purchaseTotalArray = [];
            foreach($purchases as $purchase)
            {
                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }
            for($i = 1; $i <= 12; $i++)
            {
                $purchaseTotal[] = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
            }

            $salTotal = [];
            if (module_is_active('Hrm') && empty($request->vendor)) {
                // ------------------------------------------------Employee Salary EXPENSE-------------------------------------------------

                $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
                    ->where('salary_month', 'like', $year . '-%')
                    ->where('status', 1)
                    ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

                $employees       = $employees->get();

                $employeess = [];
                foreach ($employees as $employee) {
                    $employeess[$employee->month][] = $employee->total_salary;
                }
                $salTotal = [];
                for ($i = 1; $i <= 12; $i++) {
                    // Assuming $employee->02 and $employee->03 represent different months
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $salTotal[] = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                }
            } else {
                for ($i = 1; $i <= 12; $i++) {
                    $salTotal[] = 0;
                }
            }

                $chartExpenseArr = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $expenseTotal, $billTotal, $salTotal,$purchaseTotal
                );


                $data['chartExpenseArr'] = $chartExpenseArr;
                $data['expenseArr']      = $array;
                $data['billArray']       = $billArray;
                $data['purchaseArray']   = $purchaseArray;
                $data['account']         = $account;
                $data['vendor']          = $vendor;
                $data['category']        = $category;
                if (module_is_active('Hrm')) {
                    $data['EmpSalary']       = $salTotal;
                }

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.expense_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function incomeVsExpenseSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report income vs expense manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');
                $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category =  \Modules\ProductService\Entities\Category::where('workspace_id', '=',getActiveWorkSpace())->whereIn('type', [1,2])->get()->pluck('name', 'id');
                }
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();

                $filter['category'] = __('All');
                $filter['customer'] = __('All');
                $filter['vendor']   = __('All');

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
                $expensesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                =  \Modules\ProductService\Entities\Category::find($request->category);
                    }
                    $filter['category'] = !empty($cat) ? $cat->name : '';

                }
                if(!empty($request->vendor))
                {
                    $expensesData->where('vendor_id', '=', $request->vendor);

                    $vend             = Vender::find($request->vendor);
                    $filter['vendor'] = !empty($vend) ? $vend->name : '';
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();

                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }

                // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);

                if(!empty($request->vendor))
                {
                    $bills->where('vendor_id', '=', $request->vendor);

                }

                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }

                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {
                    $bill             = [];
                    $bill['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $bill['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ?  \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }

                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }

            // ------------------------------TOTAL Purchase EXPENSE-----------------------------------------------------------

            $purchases = Purchase:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,purchase_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
            $purchases->whereRAW('YEAR(send_date) =?', [$year]);

            if(!empty($request->vendor))
            {
                $purchases->where('vendor_id', '=', $request->vendor);

            }

            if(!empty($request->category))
            {
                $purchases->where('category_id', '=', $request->category);
            }

            $purchases        = $purchases->get();
            $purchaseTmpArray = [];
            foreach($purchases as $purchase)
            {
                $purchaseTmpArray[$purchase->category_id][$purchase->month][] = $purchase->getTotal();
            }
            $purchaseArray = [];
            foreach($purchaseTmpArray as $cat_id => $record)
            {
                $purchase             = [];
                $purchase['category'] = [];
                if(module_is_active('ProductService'))
                {
                    $purchase['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ?  \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $purchase['data']     = [];
                for($i = 1; $i <= 12; $i++)
                {

                    $purchase['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $purchaseArray[] = $purchase;
            }

            $purchaseTotalArray = [];
            foreach($purchases as $purchase)
            {
                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }

                // -----------------------------------------------TOTAL EMPLOYEE EXPENSE--------------------------------------------------------

            $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
            ->where('salary_month', 'like', $year . '-%')
            ->where('status', 1)
            ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

        $employees       = $employees->get();

        $employeess = [];
        foreach ($employees as $employee) {
            $employeess[$employee->month][] = $employee->total_salary;
        }

                // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------

                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();
                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }

                // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('user_id', '=', $cust->user_id);
                }
                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {

                    $invoice             = [];
                    $invoice['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoice['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ?  \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }

                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                //        ----------------------------------------------------------------------------------------------------

                for($i = 1; $i <= 12; $i++)
            {
                $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                $billExpenseTotal[]    = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                $purchaseExpenseTotal[]    = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
                if (module_is_active('Hrm') && empty($request->vendor) && empty($request->customer)) {
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $salTotal[] = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                } else {
                    $salTotal[] = 0;
                }
                $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

                $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $RevenueIncomeTotal, $invoiceIncomeTotal
                );

                $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $paymentExpenseTotal, $billExpenseTotal, $salTotal, $purchaseExpenseTotal
                );

                $profit = [];
                $keys   = array_keys($totalIncome + $totalExpense);
                foreach($keys as $v)
                {
                    $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
                }


                $data['paymentExpenseTotal']  = $paymentExpenseTotal;
                $data['billExpenseTotal']     = $billExpenseTotal;
                $data['purchaseExpenseTotal'] = $purchaseExpenseTotal;
                $data['revenueIncomeTotal']   = $RevenueIncomeTotal;
                $data['invoiceIncomeTotal']   = $invoiceIncomeTotal;
                $data['profit']               = $profit;
                $data['account']              = $account;
                $data['vendor']               = $vendor;
                $data['customer']             = $customer;
                $data['category']             = $category;
                $data['EmpSalary']            = $salTotal;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
                return view('account::report.income_vs_expense_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function taxSummary(Request $request)
    {
        if(module_is_active('ProductService'))
        {
            if(Auth::user()->isAbleTo('report tax manage'))
            {
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                $data['taxList']   = $taxList = \Modules\ProductService\Entities\Tax::where('workspace_id', getActiveWorkSpace())->get();

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }

                $data['currentYear'] = $year;

                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();
                $incomeTaxesData = [];
                foreach($invoiceProducts as $invoiceProduct)
                {
                    $incomeTax   = [];
                    $incomeTaxes = AccountUtility::tax($invoiceProduct->tax);

                    foreach($incomeTaxes as $taxe)
                    {
                        $taxDataPrice           = AccountUtility::taxRate(!empty($taxe) ? ($taxe->rate) : 0 , $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe) ? ($taxe->name):''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }

                $income = [];
                foreach($incomeTaxesData as $month => $incomeTaxx)
                {
                    $incomeTaxRecord = [];
                    foreach($incomeTaxx as $k => $record)
                    {
                        foreach($record as $incomeTaxName => $incomeTaxAmount)
                        {
                            if(array_key_exists($incomeTaxName, $incomeTaxRecord))
                            {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            }
                            else
                            {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }

                }

                foreach($income as $incomeMonth => $incomeTaxData)
                {
                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }

                }

                $incomes = [];
                if(isset($incomeData) && !empty($incomeData))
                {
                    foreach($taxList as $taxArr)
                    {
                        foreach($incomeData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();

                $expenseTaxesData = [];
                foreach($billProducts as $billProduct)
                {
                    $billTax   = [];
                    $billTaxes = AccountUtility::tax($billProduct->tax);
                    foreach($billTaxes as $taxe)
                    {
                            $taxDataPrice         = AccountUtility::taxRate($taxe->rate, $billProduct->price, $billProduct->quantity);
                        $billTax[$taxe->name] = $taxDataPrice;
                    }
                    $expenseTaxesData[$billProduct->month][] = $billTax;
                }

                $bill = [];
                foreach($expenseTaxesData as $month => $billTaxx)
                {
                    $billTaxRecord = [];
                    foreach($billTaxx as $k => $record)
                    {
                        foreach($record as $billTaxName => $billTaxAmount)
                        {
                            if(array_key_exists($billTaxName, $billTaxRecord))
                            {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            }
                            else
                            {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }

                }

                foreach($bill as $billMonth => $billTaxData)
                {
                    $billData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }

                }
                $expenses = [];
                if(isset($billData) && !empty($billData))
                {

                    foreach($taxList as $taxArr)
                    {
                        foreach($billData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }

                    }
                }

                $data['expenses'] = $expenses;
                $data['incomes']  = $incomes;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.tax_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->route('home')->with('error', __('Please Enable Product & Service Module'));
        }

    }
    public function profitLossSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report loss & profit  manage'))
            {
                $monthList = $this->yearMonth();
                $month = $monthList;
                $month[] = __('Total');
                $data['month']     = $month;
                $data['monthList'] = $monthList;
                $data['yearList']  = $this->yearList();
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // -------------------------------REVENUE INCOME-------------------------------------------------
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $incomes->where('workspace', '=', getActiveWorkSpace());
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes        = $incomes->get();
                $tmpIncomeArray = [];
                foreach($incomes as $income)
                {
                    $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
                }

                $incomeCatMonthly   = array_fill(1, 12, 0);
                $revenueIncomeArray = array();
                foreach($tmpIncomeArray as $cat_id => $record)
                {

                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }

                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $val = array_key_exists($i, $record) ? $record[$i] : 0;
                        $incomeData[$monthList[$i - 1]] = $val;
                        $incomeCatMonthly[$i] += $val;
                    }
                    $incomeData[__('Total')] = array_sum($incomeData);

                    $tmp['amount'] = array_values($incomeData);

                    $revenueIncomeArray[] = $tmp;

                }

                $incomeCatAmount = array_values($incomeCatMonthly);
                $incomeCatAmount[] = array_sum($incomeCatAmount);
                $data['incomeCatAmount'] = $incomeCatAmount;

                $data['revenueIncomeArray'] = $revenueIncomeArray;

                //-----------------------INVOICE INCOME---------------------------------------------

                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                $invoices        = $invoices->get();

                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceCatMonthly  = array_fill(1, 12, 0);
                $invoiceIncomeArray = array();
                foreach($invoiceTmpArray as $cat_id => $record)
                {

                    $invoiceTmp             = [];
                    $invoiceTmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoiceTmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }

                    $invoiceIncomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $val = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                        $invoiceIncomeData[$monthList[$i - 1]] = $val;
                        $invoiceCatMonthly[$i] += $val;
                    }
                    $invoiceIncomeData[__('Total')] = array_sum($invoiceIncomeData);

                    $invoiceTmp['amount'] = array_values($invoiceIncomeData);

                    $invoiceIncomeArray[] = $invoiceTmp;

                }

                $invoiceCatAmount = array_values($invoiceCatMonthly);
                $invoiceCatAmount[] = array_sum($invoiceCatAmount);
                $data['invoiceIncomeCatAmount'] = $invoiceCatAmount;

                $data['invoiceIncomeArray'] = $invoiceIncomeArray;

                $data['totalIncome'] = $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $invoiceIncomeCatAmount, $incomeCatAmount
                );

                //---------------------------------PAYMENT EXPENSE-----------------------------------

                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $expenses->where('workspace', '=', getActiveWorkSpace());
                $expenses->whereRAW('YEAR(date) =?', [$year]);
                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();

                $tmpExpenseArray = [];
                foreach($expenses as $expense)
                {
                    $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
                }

                $expenseArray      = [];
                $expenseCatMonthly = array_fill(1, 12, 0);
                foreach($tmpExpenseArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }

                    $expenseData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $val = array_key_exists($i, $record) ? $record[$i] : 0;
                        $expenseData[$monthList[$i - 1]] = $val;
                        $expenseCatMonthly[$i] += $val;
                    }
                    $expenseData[__('Total')] = array_sum($expenseData);

                    $tmp['amount'] = array_values($expenseData);

                    $expenseArray[] = $tmp;

                }

                $expenseCatAmount = array_values($expenseCatMonthly);
                $expenseCatAmount[] = array_sum($expenseCatAmount);
                $data['expenseCatAmount'] = $expenseCatAmount;
                $data['expenseArray']     = $expenseArray;

                //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $bills->where('vendor_id', '=', $request->vendor);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }

                $billExpenseArray      = [];
                $billExpenseCatMonthly = array_fill(1, 12, 0);
                foreach($billTmpArray as $cat_id => $record)
                {
                    $billTmp             = [];
                    $billTmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $billTmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }

                    $billExpenseData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $val = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                        $billExpenseData[$monthList[$i - 1]] = $val;
                        $billExpenseCatMonthly[$i] += $val;
                    }
                    $billExpenseData[__('Total')] = array_sum($billExpenseData);

                    $billTmp['amount'] = array_values($billExpenseData);

                    $billExpenseArray[] = $billTmp;

                }

                $billExpenseCatAmount = array_values($billExpenseCatMonthly);
                $billExpenseCatAmount[] = array_sum($billExpenseCatAmount);
                $data['billExpenseCatAmount'] = $billExpenseCatAmount;

                $data['billExpenseArray'] = $billExpenseArray;

                $salExpenseCatAmount = array_fill(0, 13, 0);
                $salExpenseArray = [];
            if (module_is_active('Hrm')) {
                // -------------------------------------------------EXPENSE EMPLOYEE SALARY---------------------------------------------------
                $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
                    ->where('salary_month', 'like', $year . '-%')
                    ->where('status', 1)
                    ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

                $employees       = $employees->get();

                $employeess = [];
                foreach ($employees as $employee) {
                    $employeess[$employee->month][] = $employee->total_salary;
                }

                $salExpenseData = [];
                $salCatMonthly = array_fill(1, 12, 0);
                for ($i = 1; $i <= 12; $i++) {
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $val = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                    $salExpenseData[$monthList[$i - 1]] = $val;
                    $salCatMonthly[$i] = $val;
                }
                $salExpenseData[__('Total')] = array_sum($salExpenseData);

                $salTmp = [];
                $salTmp['amount'] = array_values($salExpenseData);

                $salExpenseArray[] = $salTmp;

                $salExpenseCatAmount = array_values($salCatMonthly);
                $salExpenseCatAmount[] = array_sum($salExpenseCatAmount);
                $data['salExpenseCatAmount'] = $salExpenseCatAmount;
                $data['salExpenseArray'] = $salExpenseArray;
            }

                $data['totalExpense'] = $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $billExpenseCatAmount, $expenseCatAmount, $salExpenseCatAmount
                );


                $netProfit = [];
                foreach($totalIncome as $k => $income)
                {
                    $netProfit[] = $income - $totalExpense[$k];
                }
                $data['netProfitArray'] = $netProfit;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.profit_loss_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }


    }
    public function invoiceSummary(Request $request)
    {
        if(Auth::user()->isAbleTo('report invoice manage'))
        {
            $filter['customer'] = __('All');
            $filter['status']   = __('All');

            $customer = Customer::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Invoice::$statues;

            $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if($request->status != '')
            {
                $invoices->where('status', $request->status);

                $filter['status'] = Invoice::$statues[$request->status];
            }
            else
            {
                $invoices->where('status', '!=', 0);
            }

            $invoices->where('workspace', '=', getActiveWorkSpace());

            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            if(!empty($request->customer))
            {
                $cust = Customer::find($request->customer);
                $invoices->where('user_id', $cust->user_id);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $invoices = $invoices->get();

            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach($invoices as $invoice)
            {
                $totalInvoice    += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for($i = 1; $i <= 12; $i++)
            {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();
            return view('account::report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function billSummary(Request $request)
    {
        if(Auth::user()->isAbleTo('report bill manage'))
        {
            $filter['vendor'] = __('All');
            $filter['status'] = __('All');

            $vendor = Vender::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            // Bill
            $status = Bill::$statues;

            $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year')->addselect('bills.*', 'vendors.name as vendor_name','categories.name as categories_name','bill_payments.date as lastpayment_date')->join('vendors', 'bills.vendor_id', '=', 'vendors.id')->join('categories','bills.category_id','=','categories.id')->join('bill_payments','bills.bill_id','=','bill_payments.bill_id')->where('bill_module','!=','taskly');


            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }
            $bills->where('bills.send_date', '>=', date('Y-m-01', $start))->where('bills.send_date', '<=', date('Y-m-t', $end));

            // purchase
            $status = Purchase::$statues;

            $purchases = Purchase::selectRaw('purchases.*,MONTH(send_date) as month,YEAR(send_date) as year')->addselect('purchases.*', 'categories.name as categories_name','purchase_payments.date as lastpayment_date')->join('categories','purchases.category_id','=','categories.id')->join('purchase_payments','purchases.id','=','purchase_payments.purchase_id')->where('purchase_module','!=','taskly');

            $purchases->where('purchases.send_date', '>=', date('Y-m-01', $start))->where('purchases.purchase_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if(!empty($request->vendor))
            {
                $bills->where('bills.vendor_id', $request->vendor);
                $vend = Vender::find($request->vendor);

                // purchase
                $purchases->where('purchases.vender_id', $request->vendor);

                $filter['vendor'] = !empty($vend) ? $vend->name : '';
            }

            if($request->status != '')
            {
                $bills->where('bills.status', '=', $request->status);

                $filter['status'] = Bill::$statues[$request->status];

                // purchase
                $purchases->where('purchases.status', '=', $request->status);
            }
            else
            {
                $bills->where('status', '!=', 0);

                // purchase
                $purchases->where('status', '!=', 0);
            }

            $bills->where('bills.workspace', '=', getActiveWorkSpace());
            $bills = $bills->get();

            $purchases =$purchases->where('purchases.workspace', '=', getActiveWorkSpace())->get();

            $totalBill      = 0;
            $totalDueBill   = 0;
            $billTotalArray = [];
            foreach($bills as $bill)
            {
                $totalBill    += $bill->getTotal();
                $totalDueBill += $bill->getDue();

                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            $totalPaidBill = $totalBill - $totalDueBill;
            for($i = 1; $i <= 12; $i++)
            {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            // purchase
            $totalPurchase      = 0;
            $totalDuePurchase   = 0;
            $purchaseTotalArray = [];
            foreach($purchases as $purchase)
            {
                $totalPurchase    += $purchase->getTotal();
                $totalDuePurchase += $purchase->getDue();

                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }
            $totalPaidPurchase = $totalPurchase - $totalDuePurchase;

            for($i = 1; $i <= 12; $i++)
            {
                $purchaseTotal[] = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
            }

            return view('account::report.bill_report', compact('bills', 'vendor', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter','purchases','purchaseTotal'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function productStock(Request $request)
    {
        if(Auth::user()->isAbleTo('report stock manage'))
        {
             $stocks = StockReport::where('workspace', '=', getActiveWorkSpace())->leftjoin('product_services', 'stock_reports.product_id', '=', 'product_services.id')->select('stock_reports.*', 'product_services.name as name')->get();
             return view('account::report.product_stock_report',compact('stocks'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach(range($ending_year, $starting_year) as $year)
        {
            $years[$year] = $year;
        }

        return $years;
    }
}
