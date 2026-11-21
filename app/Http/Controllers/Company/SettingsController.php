<?php

namespace App\Http\Controllers\Company;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        $timezones = config('timezones');
        $activatedModules = ActivatedModule();
        return view('company.settings.index',compact('settings','timezones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request){
        if(Auth::user()->isAbleTo('setting manage'))
        {
            $post = $request->all();
            $company_settings = getCompanyAllSetting();

            unset($post['_token']);
            unset($post['_method']);

            if(!isset($post['site_rtl'])){
                $post['site_rtl'] = 'off';
            }
            if(!isset($post['site_transparent'])){
                $post['site_transparent'] = 'off';
            }
            if(!isset($post['cust_darklayout'])){
                $post['cust_darklayout'] = 'off';
            }
            if(isset($request->color) && $request->color_flag == 'false')
            {
                $post['color'] = $request->color;
            }
            else
            {
                $post['color'] = $request->custom_color;
            }

              if (!isset($post['category_wise_sidemenu'])) {
                $post['category_wise_sidemenu'] = 'off';
            }

            if($request->hasFile('logo_dark'))
            {
                $logo_dark =  'logo_dark_'.time().'.png';
                $uplaod = upload_file($request,'logo_dark',$logo_dark,'logo');
                if($uplaod['flag'] == 1)
                {
                    $post['logo_dark'] = $uplaod['url'];

                    $old_logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : '';
                    if(!empty($old_logo_dark) && check_file($old_logo_dark))
                    {
                        delete_file($old_logo_dark);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }
            if($request->hasFile('logo_light'))
            {
                $logo_light =  'logo_light_'.time().'.png';
                $uplaod = upload_file($request,'logo_light',$logo_light,'logo');
                if($uplaod['flag'] == 1)
                {
                    $post['logo_light'] = $uplaod['url'];

                    $old_logo_light = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : '';
                    if(!empty($old_logo_light) && check_file($old_logo_light))
                    {
                        delete_file($old_logo_light);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }
            if($request->hasFile('favicon'))
            {
                $favicon =  'favicon_'.time().'.png';
                $uplaod = upload_file($request,'favicon',$favicon,'logo');
                if($uplaod['flag'] == 1){
                    $post['favicon'] = $uplaod['url'];

                    $old_favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : '';
                    if(!empty($old_favicon) && check_file($old_favicon))
                    {
                        delete_file($old_favicon);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            sideMenuCacheForget();
            return redirect()->back()->with('success', __('Setting save sucessfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function SystemStore(Request $request)
    {
        if(Auth::user()->isAbleTo('setting manage'))
        {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            if(isset($post['defult_currancy']))
            {
                $data = explode('-',$post['defult_currancy']);
                $post['defult_currancy_symbol'] = $data[0] ?? '';
                $post['defult_currancy']        = $data[1] ?? $post['defult_currancy'];
                if ($post['defult_currancy'] === 'IDR' && (empty($post['defult_currancy_symbol']) || $post['defult_currancy_symbol'] === '$')) {
                    $post['defult_currancy_symbol'] = 'Rp';
                }
            }
            if(isset($post['site_currency_symbol_position']))
            {
                $post['site_currency_symbol_position'] = !empty($request->site_currency_symbol_position) ? $request->site_currency_symbol_position : 'pre';
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
             // Settings Cache forget
             comapnySettingCacheForget();
            return redirect()->back()->with('success','Setting save sucessfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function CompanySettingStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'company_name' => 'required',
            'company_address' => 'required',
            'company_city' => 'required',
            'company_state' => 'required',
            'company_zipcode' => 'required',
            'company_country' => 'required',
            'company_telephone' => 'required',
            'company_email' => 'required',
            'company_email_from_name' => 'required',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        else
        {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            if(isset($request->vat_gst_number_switch) && $request->vat_gst_number_switch == 'on')
            {
                $post['vat_gst_number_switch'] = 'on';
                $post['tax_type'] =  !empty($request->tax_type) ? $request->tax_type : 'VAT';
                $post['vat_number'] =  !empty($request->vat_number) ? $request->vat_number : '';
            }
            else
            {
                $post['vat_gst_number_switch'] = 'off';
                $post['tax_type'] = '';
                $post['vat_number'] = '';
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success','Company setting save sucessfully.');
        }

    }

    public function saveCompanyCurrencySettings(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);
        unset($post['_method']);
        if (isset($post['defult_currancy'])) {
            $data = explode('-', $post['defult_currancy']);
            $post['defult_currancy_symbol'] = $data[0] ?? '';
            $post['defult_currancy']        = $data[1] ?? $post['defult_currancy'];
            if ($post['defult_currancy'] === 'IDR' && (empty($post['defult_currancy_symbol']) || $post['defult_currancy_symbol'] === '$')) {
                $post['defult_currancy_symbol'] = 'Rp';
            }
        }
        if (isset($post['site_currency_symbol_position'])) {
            $post['site_currency_symbol_position'] = !empty($request->site_currency_symbol_position) ? $request->site_currency_symbol_position : 'pre';
        }
        foreach ($post as $key => $value) {
            // Define the data to be updated or inserted
            $data = [
                'key' => $key,
                'workspace' => getActiveWorkSpace(),
                'created_by' => creatorId(),
            ];

            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert($data, ['value' => $value]);

            // Also keep workspace 0 in sync so settings are globally accessible
            if (getActiveWorkSpace() != 0) {
                Setting::updateOrInsert([
                    'key' => $key,
                    'workspace' => 0,
                    'created_by' => creatorId(),
                ], ['value' => $value]);
            }
        }
        // Settings Cache forget
        comapnySettingCacheForget();
        Cache::flush();
        return redirect()->back()->with('success', __('Currency Setting save successfully.'));
    }
    public function updateNoteValue(Request $request)
    {
        $symbol_position = $request->site_currency_symbol_position === 'post' ? 'post' : 'pre';
        $format = isset($request->currency_format) ? (int)$request->currency_format : 0;
        $price = '10000';

        $currency_parts = explode('-', $request->defult_currancy ?? '');
        $code = $currency_parts[1] ?? ($request->defult_currancy ?? 'IDR');
        $sym = $currency_parts[0] ?? 'Rp';
        if ($code === 'IDR' && ($sym === '$' || empty($sym))) {
            $sym = 'Rp';
        }

        if (isset($request->site_currency_symbol_name) && $request->site_currency_symbol_name === 'symbolname') {
            $symbol = $code;
        } else {
            $symbol = $sym;
        }

        $dec_setting = $request->decimal_separator ?? 'comma';
        $thou_setting = $request->thousand_separator ?? 'dot';

        $decimal_separator = ($dec_setting === 'dot') ? '.' : ',';
        $thousand_separator = ($thou_setting === 'dot') ? '.' : ',';

        $currency_space = $request->currency_space ?? 'withspace';
        $space = ($currency_space === 'withspace') ? ' ' : '';

        $formatted_price = ($symbol_position === 'pre' ? $symbol . $space : '')
            . number_format((float)$price, $format, $decimal_separator, $thousand_separator)
            . ($symbol_position === 'post' ? $space . $symbol : '');

        return response()->json(['success' => true, 'formatted_price' => $formatted_price]);
    }
}
