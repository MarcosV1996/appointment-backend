<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Fruitcake\Cors\HandleCors::class, // Ou sua classe CorsMiddleware, mas não as duas
        \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // REMOVA ESSES DO STACK GLOBAL PARA APIS, DEIXE NOS GRUPOS SE NECESSÁRIO
        // \App\Http\Middleware\SanitizeInput::class, // Se não for customizado, remova ou mova para web/api
        // \App\Http\Middleware\CorsMiddleware::class, // Você já tem o HandleCors, evite duplicidade
        // \App\Http\Middleware\VerifyCsrfToken::class, // REMOVA ISSO AQUI! Será aplicado apenas no grupo 'web'
        // \Illuminate\Routing\Middleware\SubstituteBindings::class, // Remova, já está no grupo 'api' e 'web'
        // \Illuminate\Session\Middleware\StartSession::class, // Remova, deve estar no grupo 'web'
        // \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Remova, deve estar no grupo 'web'
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class, // Fica aqui para rotas web
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // O Sanctum lida com o estado para APIs aqui
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // \Illuminate\Http\Middleware\HandleCors::class, // Se você já tem Fruitcake\Cors\HandleCors globalmente, não precisa aqui
            // \App\Http\Middleware\CorsMiddleware::class, // Se você tem Fruitcake\Cors\HandleCors, essa é redundante. Escolha uma.
        ],
        
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class, // Se você não usa JWT, remova isso. Você está usando Sanctum.
        'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class, // Se você não usa JWT, remova isso.
        
    ];
}