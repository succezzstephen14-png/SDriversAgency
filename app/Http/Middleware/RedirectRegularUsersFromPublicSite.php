<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectRegularUsersFromPublicSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isAdmin() && !$user->isCmsAdmin()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
