<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use App\Models\VoteAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VotingController extends Controller
{
    /**
     * Show the voting page with active election.
     */
    public function index()
    {
        // Check if user has already voted
        if (Auth::user()->has_voted) {
            return redirect()->route('voting.success');
        }

        // Get active election with positions and candidates
        $election = Election::where('is_active', true)
            ->with(['positions.candidates.party'])
            ->first();

        if (!$election) {
            return view('student.no-election');
        }

        return view('student.voting', compact('election'));
    }

    /**
     * Submit the votes.
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        // Check if user has already voted
        if ($user->has_voted) {
            return redirect()->route('voting.success')
                ->with('error', 'You have already voted!');
        }

        // Get active election
        $election = Election::where('is_active', true)->first();

        if (!$election) {
            return redirect()->back()
                ->with('error', 'No active election found.');
        }

        // Validate votes
        $positions = Position::where('election_id', $election->id)->get();
        
        $rules = [];
        foreach ($positions as $position) {
            if ($election->allow_abstain) {
                $rules["position_{$position->id}"] = 'nullable|exists:candidates,id';
            } else {
                $rules["position_{$position->id}"] = 'required|exists:candidates,id';
            }
        }

        $request->validate($rules);

        // Start transaction
        DB::beginTransaction();

        try {
            // Save votes
            foreach ($positions as $position) {
                $candidateId = $request->input("position_{$position->id}");
                
                if ($candidateId) {
                    Vote::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => $candidateId,
                    ]);
                    
                    // Create audit log for vote
                    VoteAuditLog::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => $candidateId,
                        'action' => 'vote_cast',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'voted_at' => now(),
                    ]);
                } else {
                    // Log abstain vote
                    VoteAuditLog::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => null,
                        'action' => 'vote_abstain',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'voted_at' => now(),
                    ]);
                }
            }

            // Mark user as voted
            $user->has_voted = true;
            $user->save();

            DB::commit();

            return redirect()->route('voting.success')
                ->with('success', 'Your vote has been recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'An error occurred while submitting your vote. Please try again.');
        }
    }

    /**
     * Show success page after voting.
     */
    public function success()
    {
        if (!Auth::user()->has_voted) {
            return redirect()->route('voting.index');
        }

        return view('student.success');
    }
}
