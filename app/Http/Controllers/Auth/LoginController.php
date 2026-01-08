<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

            $usn = $request->usn;
            $password = $request->password;

            // Try to authenticate as admin first
            $admin = Admin::where('username', $usn)->first();
            
            if ($admin && Hash::check($password, $admin->password)) {
                // Log in as admin
                Auth::guard('admin')->login($admin, $request->filled('remember'));
                
                // Regenerate session first to prevent fixation attacks
                $request->session()->regenerate();
                
                // Invalidate all other sessions for this admin
                $currentSessionId = $request->session()->getId();
                
                DB::table('sessions')
                    ->where('user_id', $admin->id)
                    ->where('id', '!=', $currentSessionId)
                    ->delete();

                \Log::info('Admin logged in (previous sessions invalidated)', [
                    'admin_id' => $admin->id,
                    'username' => $admin->username
                ]);

                return redirect()->intended('/admin/dashboard');
            }

            // Try to authenticate as student
            $student = Student::where('usn', $usn)->first();
            
            if ($student && Hash::check($password, $student->password)) {
                // Log in as student
                Auth::guard('student')->login($student, $request->filled('remember'));
                
                // Regenerate session first to prevent fixation attacks
                $request->session()->regenerate();
                
                // Invalidate all other sessions for this student
                $currentSessionId = $request->session()->getId();
                
                DB::table('sessions')
                    ->where('user_id', $student->id)
                    ->where('id', '!=', $currentSessionId)
                    ->delete();

                \Log::info('Student logged in (previous sessions invalidated)', [
                    'student_id' => $student->id,
                    'usn' => $student->usn
                ]);

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
        Auth::guard('admin')->logout();
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
