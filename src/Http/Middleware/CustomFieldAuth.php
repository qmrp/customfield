<?php

namespace Qmrp\CustomField\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomFieldAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
