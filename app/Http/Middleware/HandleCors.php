<?php

namespace Fruitcake\Cors;

use Illuminate\Http\Request;
use Closure;
namespace App\Http\Middleware;


class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Adiciona os cabeçalhos CORS à resposta
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Se for uma requisição OPTIONS, retorna uma resposta vazia com status 200
        if ($request->getMethod() === 'OPTIONS') {
            $response->setContent('');
        }

        return $response;
    }
}
