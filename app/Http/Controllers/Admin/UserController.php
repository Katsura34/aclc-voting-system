    /**
     * Return import progress as JSON (for AJAX polling)
     */
    public function importProgress(Request $request)
    {
        $importId = $request->query('import_id');
        $progress = cache()->get('import_progress_' . $importId, [
            'done' => 0,
            'total' => 0,
            'finished' => false
        ]);
        return response()->json($progress);
    }
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
            // ...existing code...
            try {
                $request->validate([
                    'csv_file' => 'required|file|mimes:csv,txt|max:2048',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // ...existing code...
                throw $e;
            }

            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            // Debug: Display message when file is being processed
            // ...existing code...

            DB::beginTransaction();

            try {
                $csv = array_map('str_getcsv', file($path));
                $header = array_shift($csv); // Remove header row
                
                // Normalize header (trim whitespace and convert to lowercase)
                $header = array_map(function($col) {
                    return strtolower(trim($col));
                }, $header);
                
                // Validate header format
                $expectedHeader = ['usn', 'lastname', 'firstname', 'strand', 'year', 'gender', 'password'];
                if ($header !== $expectedHeader) {
                    // ...existing code...
                    return redirect()->back()
                        ->with('error', 'Invalid CSV format. Expected columns: ' . implode(', ', $expectedHeader));
                }
                
                $imported = 0;
                $errors = [];
                $batch = [];
                $batchSize = 500;
                $importId = uniqid('import_', true);
                cache()->put('import_progress_' . $importId, [
                    'done' => 0,
                    'total' => count($csv),
                    'finished' => false
                ], 600);
                foreach ($csv as $index => $row) {
                    $lineNumber = $index + 2;
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    if (count($row) !== 7) {
                        $errors[] = "Line {$lineNumber}: Invalid number of columns";
                        continue;
                    }
                    list($usn, $lastname, $firstname, $strand, $year, $gender, $password) = $row;
                    $usn = trim($usn);
                    $lastname = trim($lastname);
                    $firstname = trim($firstname);
                    $strand = trim($strand);
                    $year = trim($year);
                    $gender = trim($gender);
                    $password = trim($password);
                    if (empty($usn) || empty($lastname) || empty($firstname) || empty($password)) {
                        $errors[] = "Line {$lineNumber}: USN, lastname, firstname, and password are required";
                        continue;
                    }
                    $email = $usn . '@aclc.edu.ph';
                    if (User::where('usn', $usn)->exists()) {
                        $errors[] = "Line {$lineNumber}: USN '{$usn}' already exists";
                        continue;
                    }
                    if (User::where('email', $email)->exists()) {
                        $errors[] = "Line {$lineNumber}: Email '{$email}' already exists";
                        continue;
                    }
                    if (!empty($gender) && !in_array($gender, ['Male', 'Female', 'Other'])) {
                        $errors[] = "Line {$lineNumber}: Invalid gender value. Must be Male, Female, or Other";
                        continue;
                    }
                    $batch[] = [
                        'usn' => $usn,
                        'lastname' => $lastname,
                        'firstname' => $firstname,
                        'strand' => $strand ?: null,
                        'year' => $year ?: null,
                        'gender' => $gender ?: null,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'user_type' => 'student',
                        'has_voted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (count($batch) >= $batchSize) {
                        try {
                            User::insert($batch);
                            $imported += count($batch);
                        } catch (\Exception $e) {
                            $errors[] = "Batch insert error at line {$lineNumber}: " . $e->getMessage();
                        }
                        $batch = [];
                    }
                    // Update progress in cache
                    cache()->put('import_progress_' . $importId, [
                        'done' => $imported + count($batch),
                        'total' => count($csv),
                        'finished' => false
                    ], 600);
                }
                // Insert any remaining users
                if (count($batch) > 0) {
                    try {
                        User::insert($batch);
                        $imported += count($batch);
                    } catch (\Exception $e) {
                        $errors[] = "Final batch insert error: " . $e->getMessage();
                    }
                }
                // Mark as finished
                cache()->put('import_progress_' . $importId, [
                    'done' => $imported,
                    'total' => count($csv),
                    'finished' => true
                ], 600);
                
                DB::commit();
                
                \Log::info('Users imported from CSV', [
                    'imported' => $imported,
                    'errors' => count($errors)
                ]);
                
                $message = "Successfully imported {$imported} user(s)";
                if (count($errors) > 0) {
                    $message .= ". " . count($errors) . " error(s) occurred: " . implode('; ', array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $message .= " (and " . (count($errors) - 5) . " more)";
                    }
                }
                
                // End debug message container and add JS to hide after 10s
                echo '</div><script>setTimeout(function(){var d=document.getElementById("debug-messages");if(d)d.style.display="none";},10000);</script>';
                flush();
                return redirect()->route('admin.users.index')
                    ->with(count($errors) > 0 ? 'error' : 'success', $message);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                // ...existing code...
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ...existing code...
            return redirect()->back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('CSV import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // ...existing code...
            return redirect()->back()
                ->with('error', 'Failed to import users. Please check the CSV format and try again.');
        }
    }
}
