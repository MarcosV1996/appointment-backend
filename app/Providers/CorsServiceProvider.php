<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Routing\Router;

class CorsServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
       $router->aliasMiddleware('cors', CorsMiddleware::class);
    }

    public function register()
    {
        //
    }
}
