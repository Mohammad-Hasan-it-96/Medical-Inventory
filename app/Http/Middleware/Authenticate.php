<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Never redirect API requests — let the exception handler return JSON 401.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // Web requests: redirect to the login page.
        return route('auth.login');
    }
}
