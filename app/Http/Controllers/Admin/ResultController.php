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
        try {
            $elections = Election::all();
            $selectedElection = null;
            $results = [];

            if ($request->filled('election_id')) {
                // Check if this is an AJAX request - fetch fresh data
                if ($request->has('ajax') && $request->ajax == 1) {
                    return $this->getAjaxResults($request->election_id);
                }

                // Optimized query with eager loading
                $selectedElection = Election::with([
                    'positions' => function ($query) {
                        $query->orderBy('display_order');
                    },
                    'positions.candidates.party',
                    'positions.candidates.votes' => function ($query) use ($request) {
                        $query->where('election_id', $request->election_id);
                    }
                ])->find($request->election_id);

                if ($selectedElection) {
                    foreach ($selectedElection->positions as $position) {
                        $candidateResults = [];
                        $totalVotes = 0;
                        $abstainVotes = 0;


                        // Special logic for Representative: group by strand and year_level
                        if (strtolower(trim($position->name)) === 'representative') {
                            $grouped = [];
                            foreach ($position->candidates as $candidate) {
                                $groupKey = ($candidate->course ?? 'Unknown') . ' ' . ($candidate->year_level ?? 'Unknown');
                                if (!isset($grouped[$groupKey])) {
                                    $grouped[$groupKey] = [
                                        'candidates' => [],
                                        'total_votes' => 0,
                                        'abstain_votes' => 0,
                                    ];
                                }
                                $voteCount = Vote::where('position_id', $position->id)
                                    ->where('candidate_id', $candidate->id)
                                    ->whereHas('user', function($query) use ($candidate) {
                                        $query->where('strand', $candidate->course)
                                              ->where('year', $candidate->year_level);
                                    })
                                    ->count();
                                $grouped[$groupKey]['candidates'][] = [
                                    'candidate' => $candidate,
                                    'votes' => $voteCount,
                                ];
                                $grouped[$groupKey]['total_votes'] += $voteCount;
                            }
                            // Abstain votes per group
                            foreach ($grouped as $groupKey => &$group) {
                                $firstCandidate = $group['candidates'][0]['candidate'] ?? null;
                                if ($firstCandidate) {
                                    $abstainVotes = Vote::where('position_id', $position->id)
                                        ->where('election_id', $selectedElection->id)
                                        ->whereNull('candidate_id')
                                        ->whereHas('user', function($query) use ($firstCandidate) {
                                            $query->where('strand', $firstCandidate->course)
                                                  ->where('year', $firstCandidate->year_level);
                                        })
                                        ->count();
                                    $group['abstain_votes'] = $abstainVotes;
                                    $group['total_votes'] += $abstainVotes;
                                }
                                // Sort candidates by votes
                                usort($group['candidates'], function($a, $b) {
                                    return $b['votes'] - $a['votes'];
                                });
                            }
                            $candidateResults = $grouped;
                        } else {
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
                                ->where('election_id', $selectedElection->id)
                                ->whereNull('candidate_id')
                                ->count();
                            $totalVotes += $abstainVotes;
                        }

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
        } catch (\Exception $e) {
            \Log::error('Results display error: ' . $e->getMessage(), [
                'election_id' => $request->election_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to load results. Please try again.');
        }
    }

    /**
     * Get fresh results data from database for AJAX requests.
     * This method queries the database each time to get real-time data.
     */
    private function getAjaxResults($electionId)
    {
        try {
            // Fresh query from database - no caching, optimized with eager loading
            $selectedElection = Election::with([
                'positions' => function ($query) {
                    $query->orderBy('display_order');
                },
                'positions.candidates.party'
            ])->find($electionId);

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
                    ->where('election_id', $electionId)
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
        } catch (\Exception $e) {
            \Log::error('AJAX results error: ' . $e->getMessage(), [
                'election_id' => $electionId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load results. Please try again.'
            ], 500);
        }
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
