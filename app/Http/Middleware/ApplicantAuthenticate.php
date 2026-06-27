<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicantAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isApplicant() || ! auth()->user()->is_active) {
            return redirect()->route('applicant.login')
                ->with('error', 'Please sign in to access the applicant portal.');
        }

        return $next($request);
    }
}
