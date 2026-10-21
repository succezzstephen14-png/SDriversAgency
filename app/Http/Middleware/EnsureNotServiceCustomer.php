<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotServiceCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isServiceCustomer()) {
            return redirect()->route('service.dashboard');
        }

        return $next($request);
    }
}
