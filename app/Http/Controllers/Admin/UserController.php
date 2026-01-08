<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:students,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            DB::beginTransaction();
            
            try {
                $validated['password'] = Hash::make($validated['password']);
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
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('students', 'email')->ignore($user->id)
                ],
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
}
