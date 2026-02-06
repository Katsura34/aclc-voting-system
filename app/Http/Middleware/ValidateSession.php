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
        // Skip validation for login/logout routes (including prefixed routes)
        if ($request->is('login') || 
            $request->is('logout') || 
            $request->is('*/login') || 
            $request->is('*/logout')) {
            return $next($request);
        }

        // Skip validation in testing environment with array session driver
        if (app()->environment('testing') && config('session.driver') === 'array') {
            return $next($request);
        }

        // Check for authenticated users across all guards
        $user = null;
        $guard = null;
        
        // Check each guard to find authenticated user
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $guard = 'admin';
        } elseif (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            $guard = 'student';
        } elseif (Auth::check()) {
            $user = Auth::user();
            $guard = 'web';
        }

        // Only validate session if user is authenticated
        if ($user) {
            $currentSessionId = $request->session()->getId();

            // Check if current session exists in database for this user
            // OR if this is the first request after login (no sessions in DB yet)
            $currentSessionExists = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->exists();
            
            // Check if user has ANY sessions
            $totalUserSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->count();

            // If the current session doesn't exist but user has OTHER sessions,
            // it means they logged in elsewhere - log them out
            // If user has NO sessions yet, allow it (fresh login, session not saved yet)
            if (!$currentSessionExists && $totalUserSessions > 0) {
                // Logout from the appropriate guard
                if ($guard === 'admin') {
                    Auth::guard('admin')->logout();
                } elseif ($guard === 'student') {
                    Auth::guard('student')->logout();
                } else {
                    Auth::logout();
                }
                
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Your session has been terminated because you logged in from another device or browser.');
            }
        }

        return $next($request);
    }
}
