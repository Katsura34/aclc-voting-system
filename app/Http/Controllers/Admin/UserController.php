<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

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
            // Validate input (uniqueness handled manually to allow updating existing users by name)
            $validated = $request->validate([
                'usn' => 'required|string|max:50',
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'user_type' => 'required|in:student,admin',
                'strand' => 'nullable|string|max:100',
                'year' => 'nullable|string|max:50',
                'house' => 'nullable|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
            ]);

            // If a user already exists by firstname+lastname, update only the house and return
            $existing = User::whereRaw('LOWER(firstname) = ? AND LOWER(lastname) = ?', [strtolower($validated['firstname']), strtolower($validated['lastname'])])->first();
            if ($existing) {
                if (!empty($validated['house']) && $existing->house !== $validated['house']) {
                    $existing->update(['house' => $validated['house']]);
                    Log::info('Existing user house updated via create form', ['user_id' => $existing->id, 'name' => $existing->firstname . ' ' . $existing->lastname, 'house' => $validated['house']]);
                }

                return redirect()->route('admin.users.index')
                    ->with('success', 'Existing user found — house updated.');
            }

            DB::beginTransaction();
            
            try {
                $validated['password'] = Hash::make($validated['password']);
                $validated['has_voted'] = false;

                User::create($validated);
                
                DB::commit();

                Log::info('User created', ['usn' => $validated['usn']]);

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
            Log::error('User creation error: ' . $e->getMessage(), [
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
                'house' => 'nullable|string|max:100',
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

                Log::info('User updated', ['user_id' => $user->id, 'usn' => $user->usn]);

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
            Log::error('User update error: ' . $e->getMessage(), [
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

                Log::info('User deleted', ['user_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage(), [
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

                Log::info('User vote reset', ['user_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Voting status reset successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Vote reset error: ' . $e->getMessage(), [
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

                Log::info('All votes reset', ['count' => $count]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'All voting statuses have been reset!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('All votes reset error: ' . $e->getMessage(), [
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
            
            // Add CSV headers (include 'house')
            fputcsv($file, ['usn', 'lastname', 'firstname', 'strand', 'year', 'house', 'gender', 'password']);
            
            // Add example rows
            fputcsv($file, ['2024-001', 'Doe', 'John', 'STEM', '1st Year', 'Red', 'Male', 'password123']);
            fputcsv($file, ['2024-002', 'Smith', 'Jane', 'ABM', '2nd Year', 'Blue', 'Female', 'password123']);
            
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
            try {
                $request->validate([
                    'csv_file' => 'required|file|mimes:csv,txt|max:2048',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                throw $e;
            }

            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            // Debug: Display message when file is being processed


            DB::beginTransaction();

            try {
                $csv = array_map('str_getcsv', file($path));
                $header = array_shift($csv); // Remove header row
                
                // Normalize header (trim whitespace and convert to lowercase)
                $header = array_map(function($col) {
                    return strtolower(trim($col));
                }, $header);
                
                // Validate header format (include 'house')
                $expectedHeader = ['usn', 'lastname', 'firstname', 'strand', 'year', 'house', 'gender', 'password'];
                if ($header !== $expectedHeader) {
                    return redirect()->back()
                        ->with('error', 'Invalid CSV format. Expected columns: ' . implode(', ', $expectedHeader));
                }
                
                $imported = 0;
                $updated = 0;
                $errors = [];
                $batch = [];
                $batchSize = 500;
                foreach ($csv as $index => $row) {
                    $lineNumber = $index + 2;
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    if (count($row) !== 8) {
                        $errors[] = "Line {$lineNumber}: Invalid number of columns";
                        continue;
                    }
                    list($usn, $lastname, $firstname, $strand, $year, $house, $gender, $password) = $row;
                    $usn = trim($usn);
                    $lastname = trim($lastname);
                    $firstname = trim($firstname);
                    $strand = trim($strand);
                    $year = trim($year);
                    $house = trim($house);
                    $gender = trim($gender);
                    $password = trim($password);
                    if (empty($usn) || empty($lastname) || empty($firstname) || empty($password)) {
                        $errors[] = "Line {$lineNumber}: USN, lastname, firstname, and password are required";
                        continue;
                    }
                    // Try to find an existing user by firstname + lastname (case-insensitive)
                    $existing = User::whereRaw('LOWER(firstname) = ? AND LOWER(lastname) = ?', [strtolower($firstname), strtolower($lastname)])->first();
                    if ($existing) {
                        // Update house if provided
                        if (!empty($house) && $existing->house !== $house) {
                            try {
                                $existing->update(['house' => $house]);
                                $updated++;
                            } catch (\Exception $e) {
                                $errors[] = "Line {$lineNumber}: Failed to update house for {$firstname} {$lastname}: " . $e->getMessage();
                            }
                        }
                        // Skip creating a new user for this row
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
                        'house' => $house ?: null,
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
                
                DB::commit();
                
                Log::info('Users imported from CSV', [
                    'imported' => $imported,
                    'errors' => count($errors)
                ]);
                
                $message = "Successfully imported {$imported} user(s)";
                if (isset($updated) && $updated > 0) {
                    $message .= ". Updated house for {$updated} existing user(s)";
                }
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
            Log::error('CSV import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // ...existing code...
            return redirect()->back()
                ->with('error', 'Failed to import users. Please check the CSV format and try again.');
        }
    }
}
