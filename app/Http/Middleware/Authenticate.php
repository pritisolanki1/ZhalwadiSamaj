<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use ApiResponser;

    protected function redirectTo($request): ?string
    {
        if ($request->isJson()) {
            return $this->errorResponse('Please login again.');
        } else {
            return route('login');
        }
    }
}
