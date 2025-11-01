<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Display election results.
     */
    public function index(Request $request)
    {
        $elections = Election::all();
        $selectedElection = null;
        $results = [];

        if ($request->filled('election_id')) {
            // Check if this is an AJAX request - fetch fresh data
            if ($request->has('ajax') && $request->ajax == 1) {
                return $this->getAjaxResults($request->election_id);
            }

            $selectedElection = Election::with(['positions.candidates.party', 'positions.candidates.votes'])
                ->find($request->election_id);

            if ($selectedElection) {
                foreach ($selectedElection->positions as $position) {
                    $candidateResults = [];
                    $totalVotes = 0;
                    $abstainVotes = 0;

                    foreach ($position->candidates as $candidate) {
                        $voteCount = $candidate->votes()->where('position_id', $position->id)->count();
                        $totalVotes += $voteCount;
                        
                        $candidateResults[] = [
                            'candidate' => $candidate,
                            'votes' => $voteCount,
                        ];
                    }

                    // Get abstain votes for this position
                    $abstainVotes = Vote::where('position_id', $position->id)
                        ->whereNull('candidate_id')
                        ->count();
                    
                    $totalVotes += $abstainVotes;

                    // Sort candidates by votes (descending)
                    usort($candidateResults, function($a, $b) {
                        return $b['votes'] - $a['votes'];
                    });

                    $results[] = [
                        'position' => $position,
                        'candidates' => $candidateResults,
                        'total_votes' => $totalVotes,
                        'abstain_votes' => $abstainVotes,
                    ];
                }
            }
        }

        // Get total voters and votes cast
        $totalVoters = \App\Models\User::where('user_type', 'student')->count();
        $votedCount = 0;
        
        if ($selectedElection) {
            $votedCount = \App\Models\User::where('user_type', 'student')
                ->where('has_voted', true)
                ->count();
        }

        return view('admin.results.index', compact(
            'elections',
            'selectedElection',
            'results',
            'totalVoters',
            'votedCount'
        ));
    }

    /**
     * Get fresh results data from database for AJAX requests.
     * This method queries the database each time to get real-time data.
     */
    private function getAjaxResults($electionId)
    {
        // Fresh query from database - no caching
        $selectedElection = Election::with(['positions.candidates.party'])
            ->find($electionId);

        if (!$selectedElection) {
            return response()->json(['error' => 'Election not found'], 404);
        }

        $formattedResults = [];

        foreach ($selectedElection->positions as $position) {
            $candidateResults = [];
            $totalVotes = 0;

            foreach ($position->candidates as $candidate) {
                // Fresh vote count from database
                $voteCount = Vote::where('position_id', $position->id)
                    ->where('candidate_id', $candidate->id)
                    ->count();
                
                $totalVotes += $voteCount;
                
                $candidateResults[] = [
                    'candidate' => $candidate,
                    'votes' => $voteCount,
                ];
            }

            // Fresh abstain votes count from database
            $abstainVotes = Vote::where('position_id', $position->id)
                ->whereNull('candidate_id')
                ->count();
            
            $totalVotes += $abstainVotes;

            // Sort candidates by votes (descending)
            usort($candidateResults, function($a, $b) {
                return $b['votes'] - $a['votes'];
            });

            // Format candidates with rankings
            $candidates = [];
            $rank = 1;

            foreach ($candidateResults as $candidateData) {
                $percentage = $totalVotes > 0 
                    ? round(($candidateData['votes'] / $totalVotes) * 100, 2) 
                    : 0;

                $candidates[] = [
                    'id' => $candidateData['candidate']->id,
                    'name' => $candidateData['candidate']->full_name,
                    'party' => $candidateData['candidate']->party->name ?? 'No Party',
                    'votes' => $candidateData['votes'],
                    'percentage' => $percentage,
                    'rank' => $rank++,
                ];
            }

            $abstainPercentage = $totalVotes > 0 
                ? round(($abstainVotes / $totalVotes) * 100, 2) 
                : 0;

            $formattedResults[] = [
                'position_id' => $position->id,
                'position_name' => $position->name,
                'total_votes' => $totalVotes,
                'abstain_votes' => $abstainVotes,
                'abstain_percentage' => $abstainPercentage,
                'candidates' => $candidates,
            ];
        }

        // Fresh statistics from database
        $totalVoters = \App\Models\User::where('user_type', 'student')->count();
        $votedCount = \App\Models\User::where('user_type', 'student')
            ->where('has_voted', true)
            ->count();

        $turnoutRate = $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0;

        return response()->json([
            'statistics' => [
                'totalVoters' => $totalVoters,
                'votedCount' => $votedCount,
                'turnoutRate' => $turnoutRate,
                'positionsCount' => count($formattedResults),
            ],
            'results' => $formattedResults,
        ]);
    }

    /**
     * Export results as CSV.
     */
    public function export(Request $request)
    {
        $election = Election::with(['positions.candidates.party', 'positions.candidates.votes'])
            ->find($request->election_id);

        if (!$election) {
            return redirect()->route('admin.results.index')
                ->with('error', 'Election not found!');
        }

        $filename = 'election_results_' . str_replace(' ', '_', $election->title) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($election) {
            $file = fopen('php://output', 'w');

            // Add election header
            fputcsv($file, ['Election Results: ' . $election->title]);
            fputcsv($file, ['Generated on: ' . date('F d, Y h:i A')]);
            fputcsv($file, []); // Empty line

            foreach ($election->positions as $position) {
                // Position header
                fputcsv($file, ['Position: ' . $position->name]);
                fputcsv($file, ['Candidate Name', 'Party', 'Votes', 'Percentage']);

                $totalVotes = 0;
                $candidateResults = [];

                foreach ($position->candidates as $candidate) {
                    $voteCount = $candidate->votes()->where('position_id', $position->id)->count();
                    $totalVotes += $voteCount;
                    
                    $candidateResults[] = [
                        'name' => $candidate->full_name,
                        'party' => $candidate->party->name ?? 'No Party',
                        'votes' => $voteCount,
                    ];
                }

                // Get abstain votes
                $abstainVotes = Vote::where('position_id', $position->id)
                    ->whereNull('candidate_id')
                    ->count();
                $totalVotes += $abstainVotes;

                // Sort by votes
                usort($candidateResults, function($a, $b) {
                    return $b['votes'] - $a['votes'];
                });

                // Write candidate results
                foreach ($candidateResults as $result) {
                    $percentage = $totalVotes > 0 ? round(($result['votes'] / $totalVotes) * 100, 2) : 0;
                    fputcsv($file, [
                        $result['name'],
                        $result['party'],
                        $result['votes'],
                        $percentage . '%'
                    ]);
                }

                // Abstain row
                if ($abstainVotes > 0) {
                    $percentage = $totalVotes > 0 ? round(($abstainVotes / $totalVotes) * 100, 2) : 0;
                    fputcsv($file, ['Abstain', '-', $abstainVotes, $percentage . '%']);
                }

                fputcsv($file, ['Total Votes', '', $totalVotes, '100%']);
                fputcsv($file, []); // Empty line between positions
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display printable A4 results page.
     */
    public function print(Request $request)
    {
        $election = Election::with(['positions.candidates.party', 'positions.candidates.votes'])
            ->find($request->election_id);

        if (!$election) {
            return redirect()->route('admin.results.index')
                ->with('error', 'Election not found!');
        }

        $results = [];

        foreach ($election->positions as $position) {
            $candidateResults = [];
            $totalVotes = 0;
            $abstainVotes = 0;

            foreach ($position->candidates as $candidate) {
                $voteCount = $candidate->votes()->where('position_id', $position->id)->count();
                $totalVotes += $voteCount;
                
                $candidateResults[] = [
                    'candidate' => $candidate,
                    'votes' => $voteCount,
                ];
            }

            // Get abstain votes for this position
            $abstainVotes = Vote::where('position_id', $position->id)
                ->whereNull('candidate_id')
                ->count();
            
            $totalVotes += $abstainVotes;

            // Sort candidates by votes (descending)
            usort($candidateResults, function($a, $b) {
                return $b['votes'] - $a['votes'];
            });

            $results[] = [
                'position' => $position,
                'candidates' => $candidateResults,
                'total_votes' => $totalVotes,
                'abstain_votes' => $abstainVotes,
            ];
        }

        // Get total voters and votes cast
        $totalVoters = \App\Models\User::where('user_type', 'student')->count();
        $votedCount = \App\Models\User::where('user_type', 'student')
            ->where('has_voted', true)
            ->count();

        return view('admin.results.print', compact(
            'election',
            'results',
            'totalVoters',
            'votedCount'
        ));
    }
}
