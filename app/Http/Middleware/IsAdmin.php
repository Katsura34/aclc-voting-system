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
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        // Check if user is an admin
        if (Auth::user()->user_type !== 'admin') {
            // Redirect non-admin users to their appropriate page
            return redirect()->route('voting.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
