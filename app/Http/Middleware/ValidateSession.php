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
        // Skip validation in testing environment with array session driver
        if (app()->environment('testing') && config('session.driver') === 'array') {
            return $next($request);
        }
        
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            
            // Check if current session exists in database for this user
            $sessionExists = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->exists();
            
            // If session doesn't exist or belongs to different user, logout
            if (!$sessionExists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect('/login')->with('error', 'Your session has been terminated because you logged in from another device or browser.');
            }
        }
        
        return $next($request);
    }
}
