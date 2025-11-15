<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || Auth::user()->user_type !== 'admin') {
            // Redirect non-admin users to their appropriate page
            return redirect()->route('voting.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
