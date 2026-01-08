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
        // Check if authenticated as admin
        if (!Auth::guard('admin')->check()) {
            // Redirect to login if not authenticated as admin
            return redirect()->route('login')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
