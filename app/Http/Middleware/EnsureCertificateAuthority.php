<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCertificateAuthority
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->isCertificateAuthority()) {
            abort(403, 'Certificate Authority access required.');
        }

        return $next($request);
    }
}
