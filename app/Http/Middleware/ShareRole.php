<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShareRole
{
    public function handle(Request $request, Closure $next)
    {
        view()->share('role', optional($request->user())->rol);
        return $next($request);
    }
}
