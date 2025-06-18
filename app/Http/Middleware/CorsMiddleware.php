<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
  public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:8080');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-XSRF-TOKEN');
    $response->headers->set('Access-Control-Allow-Credentials', 'true');
    
    return $response;
}
}