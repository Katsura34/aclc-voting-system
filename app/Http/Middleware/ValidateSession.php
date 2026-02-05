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
            $sessionExists = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->exists();

            // If session doesn't exist with user_id, check if it exists without user_id
            // This handles the case where session was just created but user_id wasn't set yet
            if (! $sessionExists) {
                $sessionWithoutUser = DB::table('sessions')
                    ->where('id', $currentSessionId)
                    ->whereNull('user_id')
                    ->exists();
                
                if ($sessionWithoutUser) {
                    // Update the session with the user_id
                    DB::table('sessions')
                        ->where('id', $currentSessionId)
                        ->update(['user_id' => $user->id]);
                    
                    // Now the session is valid, continue
                    return $next($request);
                }
                
                // Session doesn't exist at all or belongs to different user, logout
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
