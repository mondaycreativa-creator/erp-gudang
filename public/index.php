<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

// Auto-create .env if missing on server
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    $envContent = "APP_NAME=\"ERP Gudang\"\n"
        . "APP_ENV=production\n"
        . "APP_KEY=base64:CE9EXx7w7o2+j+zB/gYTRe5zIY7Rk6vyx4lTd9sSTi4=\n"
        . "APP_DEBUG=true\n"
        . "APP_URL=https://wikansaranaglobal.space\n\n"
        . "LOG_CHANNEL=stack\n"
        . "LOG_DEPRECATIONS_CHANNEL=null\n"
        . "LOG_LEVEL=debug\n\n"
        . "DB_CONNECTION=mysql\n"
        . "DB_HOST=localhost\n"
        . "DB_PORT=3306\n"
        . "DB_DATABASE=wikq1718_erp\n"
        . "DB_USERNAME=wikq1718_cikaluser\n"
        . "DB_PASSWORD=cikalpassword\n\n"
        . "BROADCAST_DRIVER=log\n"
        . "CACHE_DRIVER=file\n"
        . "FILESYSTEM_DISK=local\n"
        . "QUEUE_CONNECTION=sync\n"
        . "SESSION_DRIVER=file\n"
        . "SESSION_LIFETIME=120\n";
    @file_put_contents($envPath, $envContent);
}

// Auto-create modules_statuses.json if missing
$modStatuses = __DIR__ . '/../storage/modules_statuses.json';
if (!file_exists($modStatuses) && is_dir(__DIR__ . '/../storage')) {
    @file_put_contents($modStatuses, '{"ProductService":true,"LandingPage":false,"Taskly":false,"Account":true,"Hrm":false,"Lead":false,"Pos":false,"Stripe":false,"Paypal":false}');
}

// Ensure critical storage directories exist
$storageDirs = [
    __DIR__ . '/../storage/app/public',
    __DIR__ . '/../storage/framework/cache/data',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../bootstrap/cache',
];
foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Ensure platform_check.php exists if missing
$platformCheck = __DIR__ . '/../vendor/composer/platform_check.php';
if (!file_exists($platformCheck) && is_dir(dirname($platformCheck))) {
    @file_put_contents($platformCheck, "<?php // auto-generated\n");
}

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
