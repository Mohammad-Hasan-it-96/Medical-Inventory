<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepMiddleware
{
    /**
     * Allow only users with the 'rep' role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'rep') {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'You do not have permission to access this page.');
    }
}

