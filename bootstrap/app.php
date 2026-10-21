<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Use CmsAdmin middleware which checks the User::isCmsAdmin() helper
            'cms_admin' => \App\Http\Middleware\CmsAdmin::class,
            'dashboard.only' => \App\Http\Middleware\RedirectRegularUsersFromPublicSite::class,
            'service_customer' => \App\Http\Middleware\EnsureServiceCustomer::class,
            'not_service_customer' => \App\Http\Middleware\EnsureNotServiceCustomer::class,
        ]);
        $middleware->redirectGuestsTo(
            fn (\Illuminate\Http\Request $request) => $request->getHost() === config('admin.domain')
                ? route('admin.login')
                : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
