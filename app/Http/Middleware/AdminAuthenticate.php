<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()
            || ! auth()->user()->is_active
            || auth()->user()->account_status !== 'active'
            || ! auth()->user()->isAdmin()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please sign in to access the admin portal.');
        }

        return $next($request);
    }
}
