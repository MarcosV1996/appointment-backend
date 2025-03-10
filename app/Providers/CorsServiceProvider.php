<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Routing\Router;

class CorsServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
<<<<<<< HEAD
<<<<<<< HEAD
       $router->aliasMiddleware('cors', CorsMiddleware::class);
=======
        $router->aliasMiddleware('cors', CorsMiddleware::class);
>>>>>>> Initial commit - Laravel backend
=======
       $router->aliasMiddleware('cors', CorsMiddleware::class);
>>>>>>> Atualização de Testes
    }

    public function register()
    {
        //
    }
}
