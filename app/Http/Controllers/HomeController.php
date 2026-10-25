<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;

use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\MbgDispatch;
use App\Models\MbgKitchenUsage;
use App\Models\Purchase;
use Modules\ProductService\Entities\ProductService;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::check())
        {
            return redirect('dashboard');
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                return redirect()->route('login');
            }
        }
    }

    public function Dashboard()
    {
        if(Auth::check())
        {
            $user = auth()->user();

            // Eliminate super admin role: ensure user always operates as company with active workspace
            if ($user->type === 'super admin' || empty($user->active_workspace) || $user->active_workspace == 0) {
                $workspace = \App\Models\WorkSpace::first();
                $workspaceId = $workspace ? $workspace->id : 1;
                $user->type = 'company';
                $user->active_workspace = $workspaceId;
                $user->workspace_id = $workspaceId;
                $user->save();

                $companyRole = \App\Models\Role::where('name', 'company')->first();
                if ($companyRole && !$user->hasRole('company')) {
                    $user->roles()->syncWithoutDetaching([$companyRole->id]);
                }
                \Illuminate\Support\Facades\Cache::forget('sidebar_menu_' . $user->id);
            }

            $workspace = getActiveWorkSpace();
            $warehouses = Warehouse::where('workspace', $workspace)->get();
            $pusat = $warehouses->first(function($w) {
                return str_contains(strtolower($w->name), 'pusat') || str_contains(strtolower($w->name), 'koperasi') || $w->id == 1;
            }) ?? $warehouses->first();

            $sppgWarehouses = $warehouses->filter(function($w) use ($pusat) {
                return $pusat ? $w->id != $pusat->id : true;
            });

            $products = ProductService::where('workspace_id', $workspace)->with(['unit', 'category'])->get();

            $whStocks = WarehouseProduct::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->get()
                ->groupBy('product_id');

            $productMatrix = [];
            $totalAssetAll = 0;
            $totalAssetPusat = 0;
            $sppgAssetTotals = [];
            foreach ($sppgWarehouses as $swh) {
                $sppgAssetTotals[$swh->id] = 0;
            }

            foreach ($products as $p) {
                $stockPusat = isset($whStocks[$p->id]) ? ($whStocks[$p->id]->firstWhere('warehouse_id', $pusat ? $pusat->id : 1)->quantity ?? 0) : 0;
                $assetPusat = $stockPusat * ($p->purchase_price ?? 0);
                $totalAssetPusat += $assetPusat;

                $sppgStocks = [];
                $totalStock = $stockPusat;

                foreach ($sppgWarehouses as $swh) {
                    $qty = isset($whStocks[$p->id]) ? ($whStocks[$p->id]->firstWhere('warehouse_id', $swh->id)->quantity ?? 0) : 0;
                    $sppgStocks[$swh->id] = $qty;
                    $totalStock += $qty;
                    $sppgAssetTotals[$swh->id] += ($qty * ($p->purchase_price ?? 0));
                }

                $assetTotal = $totalStock * ($p->purchase_price ?? 0);
                $totalAssetAll += $assetTotal;

                $productMatrix[] = (object) [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category ? $p->category->name : '-',
                    'unit' => $p->unit ? $p->unit->name : 'Unit',
                    'purchase_price' => $p->purchase_price ?? 0,
                    'stock_pusat' => $stockPusat,
                    'sppg_stocks' => $sppgStocks,
                    'total_stock' => $totalStock,
                    'total_asset' => $assetTotal,
                ];
            }

            // Total purchases this month (Rp)
            $totalPurchasesMonth = 0;
            if (\Schema::hasTable('purchases')) {
                $purchases = Purchase::where('workspace', $workspace)
                    ->whereMonth('purchase_date', date('m'))
                    ->whereYear('purchase_date', date('Y'))
                    ->with('items')
                    ->get();
                foreach ($purchases as $pur) {
                    $totalPurchasesMonth += $pur->getTotal();
                }
            }

            // Total usage in SPPGs this month (Rp)
            $totalUsageMonth = 0;
            $usagesMonth = MbgKitchenUsage::where('workspace', $workspace)
                ->whereMonth('usage_date', date('m'))
                ->whereYear('usage_date', date('Y'))
                ->with('items.product')
                ->get();
            foreach ($usagesMonth as $us) {
                foreach ($us->items as $it) {
                    if ($it->product) {
                        $totalUsageMonth += ($it->quantity * $it->product->purchase_price);
                    }
                    $totalUsageMonth += ($it->additional_cost ?? 0);
                }
            }

            $recentDispatches = MbgDispatch::where('workspace', $workspace)
                ->with(['fromWarehouse', 'toWarehouse'])
                ->orderBy('delivery_date', 'desc')
                ->limit(5)
                ->get();

            $recentUsages = MbgKitchenUsage::where('workspace', $workspace)
                ->with(['warehouse'])
                ->orderBy('usage_date', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'warehouses', 'pusat', 'sppgWarehouses',
                'productMatrix', 'totalAssetAll', 'totalAssetPusat',
                'sppgAssetTotals', 'totalPurchasesMonth',
                'totalUsageMonth', 'recentDispatches', 'recentUsages'
            ));
        }
        else
        {
            return redirect()->route('login');
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }
        // $arrTask          = [];
        // $arrTask['label'] = [];
        // $arrTask['data']  = [];
        // foreach($arrDuration as $date => $label)
        // {
        //     $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
        //     $arrTask['label'][] = $label;
        //     $arrTask['data'][]  = $data->total;
        // }
        // return $arrTask;

        // Create an array of dates from your $arrDuration array
        $dates = array_keys($arrDuration);

        $orders = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->whereIn(DB::raw('DATE(created_at)'), $dates)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();
        // Initialize an empty $arrTask array
        $arrTask = ['label' => [], 'data' => []];

        foreach ($dates as $date) {
            $label = $arrDuration[$date];
            $total = 0;

            foreach ($orders as $item) {
                if ($item->date == $date) {
                    $total = $item->total;
                    break;
                }
            }

            $arrTask['label'][] = $label;
            $arrTask['data'][] = $total;
        }
        return $arrTask;
    }
    public function SoftwareDetails($slug)
    {
        $modules_all = Module::getByStatus(1);
        $modules = [];
        if(count($modules_all) > 0)
        {
            $modules = array_intersect_key(
                $modules_all,  // the array with all keys
                array_flip(array_rand($modules_all,(count($modules_all) <  6) ? count($modules_all) : 6 )) // keys to be extracted
            );
        }
        $plan = Plan::first();
        $addon = AddOn::where('name',$slug)->first();
        if(!empty($addon) && !empty($addon->module))
        {
            $module = Module::find($addon->module);
            if(!empty($module))
            {
                try {
                    if(module_is_active('LandingPage'))
                    {
                        return view('landingpage::marketplace.index',compact('modules','module','plan'));
                    }
                    else{
                        return view($module->getLowerName().'::marketplace.index',compact('modules','module','plan'));
                    }
                } catch (\Throwable $th) {

                }
            }
        }

        if (module_is_active('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }

        return view('marketplace.detail_not_found',compact('modules','layout'));

    }

    public function Software(Request $request)
    {
        // Get the query parameter from the request
        $query = $request->query('query');
        // Get all modules (assuming Module::getByStatus(1) returns all modules)
        $modules = Module::getByStatus(1);

        // Filter modules based on the query parameter
        if ($query) {
            $modules = array_filter($modules, function ($module) use ($query) {
                // You may need to adjust this condition based on your requirements
                return stripos($module->getName(), $query) !== false;
            });
        }
        // Rest of your code
        if (module_is_active('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }

        return view('marketplace.software', compact('modules', 'layout'));
    }

    public function Pricing()
    {
        $admin_settings = getAdminAllSetting();
        if(module_is_active('GoogleCaptcha') && (isset($admin_settings['google_recaptcha_is_on']) ? $admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
        {
            config(['captcha.secret' => isset($admin_settings['google_recaptcha_secret']) ? $admin_settings['google_recaptcha_secret'] : '']);
            config(['captcha.sitekey' => isset($admin_settings['google_recaptcha_key']) ? $admin_settings['google_recaptcha_key'] : '']);
        }
        if(Auth::check())
        {
            if(Auth::user()->type == 'company')
            {
                return redirect('plans');
            }
            else
            {
                return redirect('dashboard');
            }
        }
        else
        {
            $plan = Plan::first();
            $modules = Module::getByStatus(1);

            if (module_is_active('LandingPage')) {
                $layout = 'landingpage::layouts.marketplace';
                return view('landingpage::layouts.pricing',compact('modules','plan','layout'));

            } else {
                $layout = 'marketplace.marketplace';
            }

            return view('marketplace.pricing',compact('modules','plan','layout'));
        }
    }

    public function CustomPage(Request $request)
    {
        $modules = Module::getByStatus(1);

        if (module_is_active('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }
        if($request['page'] == 'terms_and_conditions' || $request['page'] == 'privacy_policy')
        {
            return view('custompage.'.$request['page'],compact('modules','layout'));
        }
        else
        {
            return view('marketplace.detail_not_found',compact('modules','layout'));
        }

    }

}
