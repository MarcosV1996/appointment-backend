<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            $value = strip_tags($value); // Remove tags HTML e scripts
        });

        $request->merge($input);

        return $next($request);
    }
}
