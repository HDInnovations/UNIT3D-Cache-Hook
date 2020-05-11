# UNIT3D-Cache-Hook
File based UNIT3D cache hook for before composer autoload and Laravel bootstrapping. It will push UNIT3D into light speed.

## Install

Via Composer

```bash
$ composer require hdinnovations/unit3d-cache-hook --dev
```

To install, just:
- Install this package from your UNIT3D project root using the command above.

## Setup and usage

- Create a cache folder name `coffeeCache` in `storage`. So you have this folder created: `storage/coffeeCache`. 
- Edit your `public/index.php` so it looks like so:
```php
<?php

require_once './../vendor/hdinnovations/unit3d-cache-hook/src/CoffeeCache.php';

$coffee_cache = new CoffeeCache();
$coffee_cache->cacheTime = 60 * 60 * 24 * 1;
$coffee_cache->enabledHttpStatusCodes = [
        '200',
        '202',
    ];
$coffee_cache->handle();

/**
 * Laravel - A PHP Framework For Web Artisans.
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */
define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|s
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

/** @var Illuminate\Http\Response $response */
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

if ($coffee_cache->isCacheAble()) {
    $response->sendHeaders();
    echo $response->content();
} else {
    $response->send();
}

$kernel->terminate($request, $response);

$coffee_cache->finalize();

```

- UNIT3D-Cache-Hook should now start to cache your GET Requests and creating cache files in `storage/coffeeCache`.

