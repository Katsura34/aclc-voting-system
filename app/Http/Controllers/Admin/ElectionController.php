<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    /**
     * Display a listing of elections.
     */
    public function index()
    {
        $elections = Election::withCount(['positions', 'candidates'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.elections.index', compact('elections'));
    }

    /**
     * Show the form for creating a new election.
     */
    public function create()
    {
        return view('admin.elections.create');
    }

    /**
     * Store a newly created election in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
                'allow_abstain' => 'boolean',
                'show_live_results' => 'boolean',
            ]);

            DB::beginTransaction();
            
            try {
                // If setting this election as active, deactivate others
                if ($request->has('is_active') && $request->is_active) {
                    Election::where('is_active', true)->update(['is_active' => false]);
                }

                $election = Election::create($validated);
                
                DB::commit();

                \Log::info('Election created', ['election_id' => $election->id, 'title' => $election->title]);

                return redirect()->route('admin.elections.index')
                    ->with('success', 'Election created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Election creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create election. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified election.
     */
    public function show(Election $election)
    {
        $election->load(['positions.candidates.party']);
        
        // Get vote statistics
        $totalVoters = \App\Models\User::where('user_type', 'student')->count();
        $votedCount = \App\Models\User::where('user_type', 'student')
            ->where('has_voted', true)
            ->count();

        return view('admin.elections.show', compact('election', 'totalVoters', 'votedCount'));
    }

    /**
     * Show the form for editing the specified election.
     */
    public function edit(Election $election)
    {
        return view('admin.elections.edit', compact('election'));
    }

    /**
     * Update the specified election in storage.
     */
    public function update(Request $request, Election $election)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
                'allow_abstain' => 'boolean',
                'show_live_results' => 'boolean',
            ]);

            DB::beginTransaction();
            
            try {
                // If setting this election as active, deactivate others
                if ($request->has('is_active') && $request->is_active) {
                    Election::where('id', '!=', $election->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                }

                $election->update($validated);
                
                DB::commit();

                \Log::info('Election updated', ['election_id' => $election->id, 'title' => $election->title]);

                return redirect()->route('admin.elections.index')
                    ->with('success', 'Election updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Election update error: ' . $e->getMessage(), [
                'election_id' => $election->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update election. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified election from storage.
     */
    public function destroy(Election $election)
    {
        try {
            DB::beginTransaction();
            
            try {
                // Check if election has votes
                $hasVotes = Vote::where('election_id', $election->id)->exists();
                
                if ($hasVotes) {
                    return redirect()->route('admin.elections.index')
                        ->with('error', 'Cannot delete election with existing votes!');
                }
                
                $election->delete();
                
                DB::commit();

                \Log::info('Election deleted', ['election_id' => $election->id]);

                return redirect()->route('admin.elections.index')
                    ->with('success', 'Election deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Election deletion error: ' . $e->getMessage(), [
                'election_id' => $election->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.elections.index')
                ->with('error', 'Failed to delete election. Please try again.');
        }
    }

    /**
     * Toggle election active status.
     */
    public function toggleActive(Election $election)
    {
        DB::beginTransaction();
        
        try {
            if ($election->is_active) {
                // Deactivate
                $election->is_active = false;
            } else {
                // Activate and deactivate others
                Election::where('is_active', true)->update(['is_active' => false]);
                $election->is_active = true;
            }
            
            $election->save();
            
            DB::commit();
            
            $status = $election->is_active ? 'activated' : 'deactivated';
            
            \Log::info("Election {$status}", ['election_id' => $election->id, 'title' => $election->title]);
            
            return redirect()->back()->with('success', "Election {$status} successfully!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Election toggle error: ' . $e->getMessage(), [
                'election_id' => $election->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle election status. Please try again.');
        }
    }
}
