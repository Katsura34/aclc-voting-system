<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('usn', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by voting status
        if ($request->filled('has_voted')) {
            $query->where('has_voted', $request->has_voted === 'yes' ? true : false);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'usn' => 'required|string|max:50|unique:students,usn',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'strand' => 'nullable|string|max:255',
                'year' => 'nullable|string|max:50',
                'gender' => 'nullable|string|in:Male,Female',
                'password' => 'required|string|min:8|confirmed',
            ]);

            DB::beginTransaction();
            
            try {
                $validated['password'] = Hash::make($validated['password']);
                $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
                $validated['has_voted'] = false;

                Student::create($validated);
                
                DB::commit();

                \Log::info('Student created', ['usn' => $validated['usn']]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Student created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Student creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create student. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $user)
    {
        try {
            $validated = $request->validate([
                'usn' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('students', 'usn')->ignore($user->id)
                ],
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'strand' => 'nullable|string|max:255',
                'year' => 'nullable|string|max:50',
                'gender' => 'nullable|string|in:Male,Female',
                'password' => 'nullable|string|min:8|confirmed',
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

                $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];

                $user->update($validated);
                
                DB::commit();

                \Log::info('Student updated', ['student_id' => $user->id, 'usn' => $user->usn]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Student updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Student update error: ' . $e->getMessage(), [
                'student_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update student. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $user)
    {
        try {
            DB::beginTransaction();
            
            try {
                $user->delete();
                
                DB::commit();

                \Log::info('Student deleted', ['student_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Student deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Student deletion error: ' . $e->getMessage(), [
                'student_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete student. Please try again.');
        }
    }

    /**
     * Reset voting status for a student.
     */
    public function resetVote(Student $user)
    {
        try {
            DB::beginTransaction();
            
            try {
                $user->update(['has_voted' => false]);
                
                DB::commit();

                \Log::info('Student vote reset', ['student_id' => $user->id]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'Voting status reset successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Vote reset error: ' . $e->getMessage(), [
                'student_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to reset voting status. Please try again.');
        }
    }

    /**
     * Reset voting status for all students.
     */
    public function resetAllVotes()
    {
        try {
            DB::beginTransaction();
            
            try {
                $count = Student::where('has_voted', true)->update(['has_voted' => false]);
                
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
     * Import students from CSV file.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('csv_file');
            
            if (!$file->isValid()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'The uploaded file is invalid. Please try again.');
            }
            
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            if (empty($csvData)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'The CSV file is empty.');
            }
            
            // Get headers
            $headers = array_map('trim', array_map('strtolower', array_shift($csvData)));
            
            // Validate required headers
            $requiredHeaders = ['usn', 'lastname', 'firstname', 'password'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'CSV file is missing required columns: ' . implode(', ', $missingHeaders) . '. Required format: usn,lastname,firstname,strand,year,gender,password');
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            DB::beginTransaction();
            
            try {
                foreach ($csvData as $index => $row) {
                    $rowNumber = $index + 2;
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Ensure row has the same number of columns as headers
                    if (count($row) !== count($headers)) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: Column count mismatch.";
                        continue;
                    }

                    // Create associative array
                    $data = array_combine($headers, array_map('trim', $row));

                    // Validate row data
                    $validator = Validator::make($data, [
                        'usn' => 'required|string|max:50|unique:students,usn',
                        'lastname' => 'required|string|max:255',
                        'firstname' => 'required|string|max:255',
                        'strand' => 'nullable|string|max:255',
                        'year' => 'nullable|string|max:50',
                        'gender' => 'nullable|string|in:Male,Female,male,female',
                        'password' => 'required|string|min:1',
                    ]);

                    if ($validator->fails()) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Create student
                    Student::create([
                        'usn' => $data['usn'],
                        'first_name' => $data['firstname'],
                        'last_name' => $data['lastname'],
                        'name' => $data['firstname'] . ' ' . $data['lastname'],
                        'strand' => $data['strand'] ?? null,
                        'year' => $data['year'] ?? null,
                        'gender' => isset($data['gender']) ? ucfirst(strtolower($data['gender'])) : null,
                        'password' => Hash::make($data['password']),
                        'has_voted' => false,
                    ]);

                    $importedCount++;
                }
                
                DB::commit();

                $message = "Successfully imported {$importedCount} student(s).";
                if ($skippedCount > 0) {
                    $message .= " Skipped {$skippedCount} row(s) due to validation errors.";
                }

                if (!empty($errors)) {
                    session()->flash('import_errors', $errors);
                }

                \Log::info('Students imported', [
                    'imported' => $importedCount,
                    'skipped' => $skippedCount
                ]);

                return redirect()->route('admin.users.index')
                    ->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.users.index')
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Student import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Error importing CSV: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV template for student import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_template.csv"',
        ];

        $columns = ['usn', 'lastname', 'firstname', 'strand', 'year', 'gender', 'password'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data
            fputcsv($file, [
                'USN-2024-0001',
                'Dela Cruz',
                'Juan',
                'STEM',
                '1st Year',
                'Male',
                'password123',
            ]);
            
            fputcsv($file, [
                'USN-2024-0002',
                'Santos',
                'Maria',
                'ABM',
                '2nd Year',
                'Female',
                'password456',
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
