<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ValidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation for login/logout routes
        if ($request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        // Skip validation in testing environment with array session driver
        if (app()->environment('testing') && config('session.driver') === 'array') {
            return $next($request);
        }

        // Determine the authenticated user across all guards
        $user = null;
        foreach (['admin', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                break;
            }
        }

        // Only validate session for authenticated users
        if ($user) {
            $currentSessionId = $request->session()->getId();

            // Check if current session exists in database for this user
            $sessionExists = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->exists();

            // If session doesn't exist or belongs to different user, logout
            if (! $sessionExists) {
                Auth::guard('admin')->logout();
                Auth::guard('student')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Your session has been terminated because you logged in from another device or browser.');
            }
        }

        return $next($request);
    }
}
