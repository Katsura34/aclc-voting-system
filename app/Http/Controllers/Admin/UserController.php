<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAuditLog;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use LogsAdminActions;

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
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
        $validated = $request->validate([
            'student_id' => 'required|string|max:50|unique:users,student_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:student,admin',
            'year_level' => 'nullable|string|max:50',
            'course' => 'nullable|string|max:100',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['has_voted'] = false;

        $user = User::create($validated);

        $this->logAdminAction(
            'create',
            "Created user: {$user->name} ({$user->usn})",
            User::class,
            $user->id,
            null,
            $validated
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
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
        $oldValues = $user->only(['student_id', 'first_name', 'last_name', 'email', 'user_type', 'year_level', 'course', 'has_voted']);

        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'student_id')->ignore($user->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'user_type' => 'required|in:student,admin',
            'year_level' => 'nullable|string|max:50',
            'course' => 'nullable|string|max:100',
            'has_voted' => 'boolean',
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $this->logAdminAction(
            'update',
            "Updated user: {$user->name} ({$user->usn})",
            User::class,
            $user->id,
            $oldValues,
            $user->only(['student_id', 'first_name', 'last_name', 'email', 'user_type', 'year_level', 'course', 'has_voted'])
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the currently logged-in user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $userName = $user->name;
        $userUsn = $user->usn;

        $user->delete();

        $this->logAdminAction(
            'delete',
            "Deleted user: {$userName} ({$userUsn})",
            User::class,
            $user->id
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Reset voting status for a user.
     */
    public function resetVote(User $user)
    {
        // Get user's votes before resetting
        $votes = Vote::where('user_id', $user->id)->get();

        // Log each vote reset
        foreach ($votes as $vote) {
            VoteAuditLog::create([
                'user_id' => $user->id,
                'election_id' => $vote->election_id,
                'position_id' => $vote->position_id,
                'candidate_id' => $vote->candidate_id,
                'action' => 'vote_reset_by_admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'voted_at' => now(),
            ]);
        }

        // Delete votes
        Vote::where('user_id', $user->id)->delete();

        $user->update(['has_voted' => false]);

        $this->logAdminAction(
            'reset_vote',
            "Reset votes for user: {$user->name} ({$user->usn})",
            User::class,
            $user->id
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Voting status reset successfully!');
    }

    /**
     * Reset voting status for all users.
     */
    public function resetAllVotes()
    {
        // Get all votes before resetting
        $votes = Vote::all();

        // Log each vote reset
        foreach ($votes as $vote) {
            VoteAuditLog::create([
                'user_id' => $vote->user_id,
                'election_id' => $vote->election_id,
                'position_id' => $vote->position_id,
                'candidate_id' => $vote->candidate_id,
                'action' => 'vote_reset_all_by_admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'voted_at' => now(),
            ]);
        }

        $voteCount = $votes->count();

        // Delete all votes
        Vote::truncate();

        User::where('user_type', 'student')->update(['has_voted' => false]);

        $this->logAdminAction(
            'reset_all_votes',
            "Reset all votes ({$voteCount} total votes reset)"
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'All voting statuses have been reset!');
    }
}
