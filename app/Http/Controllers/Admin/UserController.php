<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('usn', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by voting status
        if ($request->filled('has_voted')) {
            $query->where('has_voted', $request->has_voted === 'yes' ? true : false);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'usn' => 'required|string|max:50|unique:users,usn',
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'user_type' => 'required|in:student,admin',
                'strand' => 'nullable|string|max:100',
                'year' => 'nullable|string|max:50',
                'gender' => 'nullable|in:Male,Female,Other',
            ]);

            DB::beginTransaction();
            
            try {
                $validated['password'] = Hash::make($validated['password']);
                $validated['has_voted'] = false;

                User::create($validated);
                
                DB::commit();

                \Log::info('User created', ['usn' => $validated['usn']]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'usn' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('users', 'usn')->ignore($user->id)
                ],
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id)
                ],
                'password' => 'nullable|string|min:8|confirmed',
                'user_type' => 'required|in:student,admin',
                'strand' => 'nullable|string|max:100',
                'year' => 'nullable|string|max:50',
                'gender' => 'nullable|in:Male,Female,Other',
                'has_voted' => 'boolean',
            ]);

            DB::beginTransaction();
            
            try {
                // Only update password if provided
                if ($request->filled('password')) {
                    $validated['password'] = Hash::make($validated['password']);
                } else {
                    unset($validated['password']);
                }

                $user->update($validated);
                
                DB::commit();

                \Log::info('User updated', ['user_id' => $user->id, 'usn' => $user->usn]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting the currently logged-in user
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete your own account!');
            }

            DB::beginTransaction();
            
            try {
                $user->delete();
                
                DB::commit();

                \Log::info('User deleted', ['user_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('User deletion error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    /**
     * Reset voting status for a user.
     */
    public function resetVote(User $user)
    {
        try {
            DB::beginTransaction();
            
            try {
                $user->update(['has_voted' => false]);
                
                DB::commit();

                \Log::info('User vote reset', ['user_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Voting status reset successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Vote reset error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to reset voting status. Please try again.');
        }
    }

    /**
     * Reset voting status for all users.
     */
    public function resetAllVotes()
    {
        try {
            DB::beginTransaction();
            
            try {
                $count = User::where('user_type', 'student')->update(['has_voted' => false]);
                
                DB::commit();

                \Log::info('All votes reset', ['count' => $count]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'All voting statuses have been reset!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('All votes reset error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to reset all voting statuses. Please try again.');
        }
    }

    /**
     * Download CSV template for bulk import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['usn', 'lastname', 'firstname', 'strand', 'year', 'gender', 'password']);
            
            // Add example row
            fputcsv($file, ['2024-001', 'Doe', 'John', 'STEM', '1st Year', 'Male', 'password123']);
            fputcsv($file, ['2024-002', 'Smith', 'Jane', 'ABM', '2nd Year', 'Female', 'password123']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import users from CSV file.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('csv_file');
            
            // Generate a unique job ID
            $jobId = uniqid('import_', true);
            
            // Store the CSV file temporarily
            $path = $file->storeAs('imports', $jobId . '.csv');
            $fullPath = storage_path('app/' . $path);
            
            // Dispatch the import job
            \App\Jobs\ImportUsersJob::dispatch($jobId, $fullPath);
            
            \Log::info('User import job dispatched', ['job_id' => $jobId]);
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'job_id' => $jobId,
                    'message' => 'Import job started successfully'
                ]);
            }
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Import started! Job ID: ' . $jobId)
                ->with('import_job_id', $jobId);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('CSV import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start import. Please try again.'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to start import. Please check the CSV format and try again.');
        }
    }

    /**
     * Get import progress for a job.
     */
    public function importProgress(Request $request, string $jobId)
    {
        $progress = DB::table('import_progress')
            ->where('job_id', $jobId)
            ->first();

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'percentage' => 0,
                'message' => 'Import job not found',
            ], 404);
        }

        $percentage = $progress->total_rows > 0
            ? round(($progress->processed_rows / $progress->total_rows) * 100, 2)
            : 0;

        $errors = $progress->errors ? json_decode($progress->errors, true) : [];

        return response()->json([
            'status' => $progress->status,
            'percentage' => $percentage,
            'total_rows' => $progress->total_rows,
            'processed_rows' => $progress->processed_rows,
            'imported_count' => $progress->imported_count,
            'error_count' => $progress->error_count,
            'errors' => array_slice($errors, 0, 5), // Return first 5 errors
            'message' => $this->getProgressMessage($progress, $percentage),
        ]);
    }

    /**
     * Get progress message based on status.
     */
    protected function getProgressMessage($progress, $percentage): string
    {
        switch ($progress->status) {
            case 'processing':
                return "Processing... {$percentage}% complete ({$progress->processed_rows}/{$progress->total_rows} rows)";
            case 'completed':
                $message = "Import completed! {$progress->imported_count} user(s) imported successfully.";
                if ($progress->error_count > 0) {
                    $message .= " {$progress->error_count} error(s) occurred.";
                }
                return $message;
            case 'failed':
                return "Import failed. Please check the error messages.";
            default:
                return "Unknown status";
        }
    }
}
