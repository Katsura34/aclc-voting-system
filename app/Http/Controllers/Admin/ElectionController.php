<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use App\Models\Vote;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    private const DATETIME_LOCAL_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * Display a listing of elections.
     */
    public function index()
    {
        $elections = Election::withCount(['positions'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Compute candidate count for each election through pivot
        foreach ($elections as $election) {
            $positionIds = $election->positions()->pluck('positions.id');
            $election->candidates_count = \App\Models\Candidate::whereIn('position_id', $positionIds)->count();
        }

        return view('admin.elections.index', compact('elections'));
    }

    /**
     * Show the form for creating a new election.
     */
    public function create()
    {
        $positions = Position::orderBy('name')->get();
        $parties = Party::orderBy('name')->get();

        return view('admin.elections.create', compact('positions', 'parties'));
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
                'start_date' => 'required|date_format:'.self::DATETIME_LOCAL_FORMAT,
                'end_date' => 'required|date_format:'.self::DATETIME_LOCAL_FORMAT,
                'is_active' => 'boolean',
                'positions' => 'nullable|array',
                'positions.*' => 'exists:positions,id',
                'parties' => 'nullable|array',
                'parties.*' => 'exists:parties,id',
            ]);

            $validated = $this->normalizeDates($validated);

            DB::beginTransaction();
            
            try {
                // If setting this election as active, deactivate others
                if ($request->has('is_active') && $request->is_active) {
                    Election::where('is_active', true)->update(['is_active' => false]);
                }

                $election = Election::create($validated);

                // Sync positions with display order
                if ($request->has('positions')) {
                    $positionData = [];
                    foreach ($request->positions as $index => $positionId) {
                        $positionData[$positionId] = ['display_order' => $index];
                    }
                    $election->positions()->sync($positionData);
                }

                // Sync parties
                if ($request->has('parties')) {
                    $election->parties()->sync($request->parties);
                }
                
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
        $election->load(['positions.candidates.party', 'parties']);
        
        // Get vote statistics using Student model
        $totalVoters = \App\Models\Student::count();
        $votedCount = \App\Models\Student::where('has_voted', true)->count();

        return view('admin.elections.show', compact('election', 'totalVoters', 'votedCount'));
    }

    /**
     * Show the form for editing the specified election.
     */
    public function edit(Election $election)
    {
        $positions = Position::orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        $election->load(['positions', 'parties']);

        return view('admin.elections.edit', compact('election', 'positions', 'parties'));
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
                'start_date' => 'required|date_format:'.self::DATETIME_LOCAL_FORMAT,
                'end_date' => 'required|date_format:'.self::DATETIME_LOCAL_FORMAT,
                'is_active' => 'boolean',
                'positions' => 'nullable|array',
                'positions.*' => 'exists:positions,id',
                'parties' => 'nullable|array',
                'parties.*' => 'exists:parties,id',
            ]);

            $validated = $this->normalizeDates($validated);

            DB::beginTransaction();
            
            try {
                // If setting this election as active, deactivate others
                if ($request->has('is_active') && $request->is_active) {
                    Election::where('id', '!=', $election->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                }

                $election->update($validated);

                // Sync positions with display order
                $positionData = [];
                if ($request->has('positions')) {
                    foreach ($request->positions as $index => $positionId) {
                        $positionData[$positionId] = ['display_order' => $index];
                    }
                }
                $election->positions()->sync($positionData);

                // Sync parties
                $election->parties()->sync($request->parties ?? []);
                
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
     * Normalize and validate datetime-local fields.
     */
    private function normalizeDates(array $validated): array
    {
        try {
            $validated['start_date'] = Carbon::createFromFormat(self::DATETIME_LOCAL_FORMAT, $validated['start_date']);
            $validated['end_date'] = Carbon::createFromFormat(self::DATETIME_LOCAL_FORMAT, $validated['end_date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'start_date' => 'Invalid date format.',
                'end_date' => 'Invalid date format.',
            ]);
        }

        if ($validated['end_date']->lessThanOrEqualTo($validated['start_date'])) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be after the start date.',
            ]);
        }

        return $validated;
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
