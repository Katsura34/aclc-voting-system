<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'usn' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = [
                'usn' => $request->usn,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                // Redirect based on user type
                if (Auth::user()->user_type === 'admin') {
                    return redirect()->intended('/admin/dashboard');
                }

                return redirect()->intended(route('voting.index'));
            }

            throw ValidationException::withMessages([
                'usn' => 'The provided credentials do not match our records.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'usn' => $request->usn,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput($request->only('usn', 'remember'))
                ->withErrors(['usn' => 'An error occurred during login. Please try again.']);
        }
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
