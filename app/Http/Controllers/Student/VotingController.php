<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
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
        try {
            $user = Auth::user();
            
            // Check if user has already voted
            if ($user->has_voted) {
                return redirect()->route('voting.success');
            }

            // Get active election with caching for better performance
            $election = Election::getActiveElection();

            if (!$election) {
                return view('student.no-election');
            }

            return view('student.voting', compact('election'));
        } catch (\Exception $e) {
            \Log::error('Voting index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('student.no-election')
                ->with('error', 'An error occurred while loading the voting page. Please try again or contact support.');
        }
    }

    /**
     * Submit the votes.
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        try {
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

            // Validate votes - required only if position has candidates
            $positions = Position::where('election_id', $election->id)->get();
            $rules = [];
            foreach ($positions as $position) {
                if ($position->candidates()->count() > 0) {
                    if ($position->max_winners > 1) {
                        // Expect an array of candidate ids for multi-winner positions
                        $rules["position_{$position->id}"] = 'required|array|min:1';
                        $rules["position_{$position->id}.*"] = 'distinct|exists:candidates,id';
                    } else {
                        $rules["position_{$position->id}"] = 'required|exists:candidates,id';
                    }
                }
            }
            $validated = $request->validate($rules);

            // Start transaction
            DB::beginTransaction();

            try {
                // Save votes (support multi-winner positions)
                foreach ($positions as $position) {
                    if ($position->candidates()->count() === 0) {
                        continue;
                    }

                    if ($position->max_winners > 1) {
                        $candidateIds = $request->input("position_{$position->id}", []);
                        if (!is_array($candidateIds)) {
                            $candidateIds = [$candidateIds];
                        }

                        // Enforce general max winners for the position
                        if (count($candidateIds) > $position->max_winners) {
                            return redirect()->back()
                                ->with('error', "You may only choose up to {$position->max_winners} candidate(s) for {$position->name}.")
                                ->withInput();
                        }

                        // Enforce STEM-specific cap if the student belongs to STEM strand
                        $studentStrand = strtolower(trim($user->strand ?? ''));
                        if ($studentStrand === 'stem') {
                            $selectedCandidates = Candidate::whereIn('id', $candidateIds)->get();
                            $stemSelected = $selectedCandidates->filter(function ($c) {
                                return strtolower(trim($c->course ?? '')) === 'stem';
                            })->count();

                            if ($stemSelected > 2) {
                                return redirect()->back()
                                    ->with('error', "You may only choose up to 2 STEM candidate(s) for {$position->name}.")
                                    ->withInput();
                            }
                        }

                        foreach ($candidateIds as $candidateId) {
                            Vote::create([
                                'user_id' => $user->id,
                                'election_id' => $election->id,
                                'position_id' => $position->id,
                                'candidate_id' => $candidateId,
                            ]);

                            // Create audit log entry per candidate
                            $candidate = Candidate::find($candidateId);
                            AuditLog::create([
                                'user_id' => $user->id,
                                'election_id' => $election->id,
                                'position_id' => $position->id,
                                'candidate_id' => $candidateId,
                                'action_type' => 'vote_cast',
                                'user_usn' => $user->usn,
                                'user_name' => $user->full_name,
                                'candidate_name' => $candidate ? $candidate->full_name : null,
                                'position_name' => $position->name,
                                'ip_address' => $request->ip(),
                                'user_agent' => $request->userAgent(),
                                'voted_at' => now(),
                            ]);
                        }
                    } else {
                        $candidateId = $request->input("position_{$position->id}");

                        Vote::create([
                            'user_id' => $user->id,
                            'election_id' => $election->id,
                            'position_id' => $position->id,
                            'candidate_id' => $candidateId,
                        ]);

                        // Create audit log entry
                        $candidate = Candidate::find($candidateId);
                        AuditLog::create([
                            'user_id' => $user->id,
                            'election_id' => $election->id,
                            'position_id' => $position->id,
                            'candidate_id' => $candidateId,
                            'action_type' => 'vote_cast',
                            'user_usn' => $user->usn,
                            'user_name' => $user->full_name,
                            'candidate_name' => $candidate ? $candidate->full_name : null,
                            'position_name' => $position->name,
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

                \Log::info('Vote submitted successfully', [
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                ]);

                return redirect()->route('voting.success')
                    ->with('success', 'Your vote has been recorded successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                
                \Log::error('Vote submission error (database): ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()
                    ->with('error', 'An error occurred while submitting your vote. Please try again.')
                    ->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Vote submission error (general): ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again or contact support.')
                ->withInput();
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
