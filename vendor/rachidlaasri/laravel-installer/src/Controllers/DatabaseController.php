<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use Nwidart\Modules\Facades\Module;
class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
       $response = $this->databaseManager->migrateAndSeed();
       $modules =  Module::all();
       if(count($modules)>0)
       {
           foreach ($modules as $key => $module)
           {
             $module->enable();
           }
       }
        $module_json=Module::getByStatus(1);
        if(count($module_json)>0)
        {
            return redirect()->route('LaravelInstaller::default_module', ['module' => 'LandingPage']);
        }
        else
        {
            return redirect()->route('LaravelInstaller::final')
                         ->with(['message' => $response]);
        }
    }
}
