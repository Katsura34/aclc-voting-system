<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class StudentLoginController extends Controller
{
    /**
     * Show the student login form.
     */
    public function showLoginForm()
    {
        // If already logged in as student, redirect to voting
        if (Auth::guard('student')->check()) {
            return redirect()->route('voting.index');
        }

        return view('auth.student-login');
    }

    /**
     * Handle student login request.
     */
    public function login(Request $request)
    {
        try {
            // Rate limiting - max 10 attempts per minute per IP
            $key = 'student-login.' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 10)) {
                $seconds = RateLimiter::availableIn($key);
                
                \Log::warning('Student login rate limit exceeded', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                throw ValidationException::withMessages([
                    'usn' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
            }

            $request->validate([
                'usn' => 'required|string',
                'password' => 'required|string',
            ]);

            $usn = $request->usn;
            $password = $request->password;

            // Find student
            $student = Student::where('usn', $usn)->first();
            
            if (!$student || !Hash::check($password, $student->password)) {
                // Increment rate limiter on failed attempt
                RateLimiter::hit($key, 60);
                
                // Log failed attempt (less verbose than admin)
                \Log::info('Failed student login attempt', [
                    'usn' => $usn,
                    'ip' => $request->ip()
                ]);
                
                throw ValidationException::withMessages([
                    'usn' => 'The provided credentials do not match our records.',
                ]);
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            // Log in as student
            Auth::guard('student')->login($student, $request->filled('remember'));
            
            // Regenerate session to prevent fixation attacks
            $request->session()->regenerate();
            
            // Invalidate all other sessions for this student (single session only)
            $currentSessionId = $request->session()->getId();
            
            DB::table('sessions')
                ->where('user_id', $student->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();

            // Log successful student login
            \Log::info('Student logged in successfully', [
                'student_id' => $student->id,
                'usn' => $student->usn
            ]);

            return redirect()->intended(route('voting.index'));

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Student login error: ' . $e->getMessage(), [
                'usn' => $request->usn ?? 'N/A',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput($request->only('usn', 'remember'))
                ->withErrors(['usn' => 'An error occurred during login. Please try again.']);
        }
    }

    /**
     * Handle student logout request.
     */
    public function logout(Request $request)
    {
        $studentId = Auth::guard('student')->id();
        
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Log::info('Student logged out', [
            'student_id' => $studentId
        ]);

        return redirect()->route('student.login');
    }
}
