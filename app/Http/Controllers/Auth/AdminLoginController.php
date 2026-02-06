<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    /**
     * Handle admin login request with enhanced security.
     */
    public function login(Request $request)
    {
        try {
            // Rate limiting - max 5 attempts per minute per IP
            $key = 'admin-login.' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                
                \Log::warning('Admin login rate limit exceeded', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                throw ValidationException::withMessages([
                    'username' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
            }

            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $username = $request->username;
            $password = $request->password;

            // Find admin
            $admin = Admin::where('username', $username)->first();
            
            if (!$admin || !Hash::check($password, $admin->password)) {
                // Increment rate limiter on failed attempt
                RateLimiter::hit($key, 60);
                
                // Log failed attempt
                \Log::warning('Failed admin login attempt', [
                    'username' => $username,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                throw ValidationException::withMessages([
                    'username' => 'The provided credentials do not match our records.',
                ]);
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            // Log in as admin
            Auth::guard('admin')->login($admin, $request->filled('remember'));
            
            // Regenerate session to prevent fixation attacks
            $request->session()->regenerate();
            
            // Invalidate all other sessions for this admin (single session only)
            $currentSessionId = $request->session()->getId();
            
            DB::table('sessions')
                ->where('user_id', $admin->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();

            // Log successful admin login
            \Log::info('Admin logged in successfully', [
                'admin_id' => $admin->id,
                'username' => $admin->username,
                'ip' => $request->ip()
            ]);

            return redirect()->intended(route('admin.dashboard'));

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Admin login error: ' . $e->getMessage(), [
                'username' => $request->username ?? 'N/A',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'An error occurred during login. Please try again.']);
        }
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Log::info('Admin logged out', [
            'admin_id' => $adminId
        ]);

        return redirect()->route('admin.login');
    }
}
