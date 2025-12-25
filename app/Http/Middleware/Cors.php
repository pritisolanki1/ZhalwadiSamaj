<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;

class Cors extends Middleware
{
    public function handle($request, Closure $next): mixed
    {
        return $next($request)->header('Access-Control-Allow-Origin', '*')->header(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, DELETE, OPTIONS'
        )->header(
            'Access-Control-Allow-Headers',
            ' Origin, Content-Type, Accept, Authorization, X-Request-With'
        )->header(
            'Access-Control-Allow-Credentials',
            ' true'
        );
    }
}
