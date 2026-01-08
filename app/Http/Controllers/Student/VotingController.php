<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use App\Models\VotingRecord;
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
            $student = Auth::guard('student')->user();
            
            // Check if student has already voted
            if ($student->has_voted) {
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
        $student = Auth::guard('student')->user();

        try {
            // Check if student has already voted
            if ($student->has_voted) {
                return redirect()->route('voting.success')
                    ->with('error', 'You have already voted!');
            }

            // Get active election
            $election = Election::where('is_active', true)->first();

            if (!$election) {
                return redirect()->back()
                    ->with('error', 'No active election found.');
            }

            // Validate votes - all positions are required
            $positions = Position::where('election_id', $election->id)->get();
            
            $rules = [];
            foreach ($positions as $position) {
                $rules["position_{$position->id}"] = 'required|exists:candidates,id';
            }

            $validated = $request->validate($rules);

            // Start transaction
            DB::beginTransaction();

            try {
                // Save votes
                foreach ($positions as $position) {
                    $candidateId = $request->input("position_{$position->id}");
                    
                    Vote::create([
                        'student_id' => $student->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => $candidateId,
                    ]);
                }

                // Create voting record for manual counting backup
                VotingRecord::create([
                    'election_id' => $election->id,
                    'student_id' => $student->id,
                    'voted_at' => now(),
                    'ip_address' => $request->ip(),
                ]);

                // Mark student as voted
                $student->has_voted = true;
                $student->save();

                DB::commit();

                \Log::info('Vote submitted successfully', [
                    'student_id' => $student->id,
                    'election_id' => $election->id,
                ]);

                return redirect()->route('voting.success')
                    ->with('success', 'Your vote has been recorded successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                
                \Log::error('Vote submission error (database): ' . $e->getMessage(), [
                    'student_id' => $student->id,
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
                'student_id' => $student->id,
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
        if (!Auth::guard('student')->user()->has_voted) {
            return redirect()->route('voting.index');
        }

        return view('student.success');
    }
}
