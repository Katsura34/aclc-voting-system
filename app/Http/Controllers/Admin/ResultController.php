<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

                        // Special logic for Representative: group by course + year and count votes only from matching students
                        if (strtolower(trim($position->name)) === 'representative') {
                            $groups = [];

                            foreach ($position->candidates as $candidate) {
                                $course = $candidate->course ?? 'Unknown';
                                $year = $candidate->year_level ?? 'Unknown';
                                $groupKey = $course . ' ' . $year;

                                $voteCount = Vote::where('position_id', $position->id)
                                    ->where('election_id', $selectedElection->id)
                                    ->where('candidate_id', $candidate->id)
                                    ->whereHas('user', function($query) use ($candidate) {
                                        $query->where('strand', $candidate->course)
                                              ->where('year', $candidate->year_level);
                                    })
                                    ->count();

                                if (!isset($groups[$groupKey])) {
                                    $groups[$groupKey] = [
                                        'course' => $course,
                                        'year' => $year,
                                        'candidates' => [],
                                        'group_total_votes' => 0,
                                        'abstain_votes' => 0,
                                    ];
                                }

                                $groups[$groupKey]['candidates'][] = [
                                    'candidate' => $candidate,
                                    'votes' => $voteCount,
                                ];

                                $groups[$groupKey]['group_total_votes'] += $voteCount;
                                $totalVotes += $voteCount;
                            }

                            // Sort candidates in each group by votes desc
                            foreach ($groups as $key => $group) {
                                usort($groups[$key]['candidates'], function($a, $b) {
                                    return $b['votes'] - $a['votes'];
                                });
                            }

                            $results[] = [
                                'position' => $position,
                                'groups' => $groups,
                                'total_votes' => $totalVotes,
                                'abstain_votes' => 0,
                            ];
                        } else {
                            foreach ($position->candidates as $candidate) {
                                $voteCount = $candidate->votes()
                                    ->where('position_id', $position->id)
                                    ->where('election_id', $selectedElection->id)
                                    ->count();
                                $totalVotes += $voteCount;
                                $candidateResults[] = [
                                    'candidate' => $candidate,
                                    'votes' => $voteCount,
                                ];
                            }

                            // No abstain votes in database; skip abstain counting

                            // Sort candidates by votes (descending)
                            usort($candidateResults, function($a, $b) {
                                return $b['votes'] - $a['votes'];
                            });

                            $results[] = [
                                'position' => $position,
                                'candidates' => $candidateResults,
                                'total_votes' => $totalVotes,
                                'abstain_votes' => 0,
                            ];
                        }
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
            Log::error('Results display error: ' . $e->getMessage(), [
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
                $totalVotes = 0;

                // Representative special handling: group by course+year and only count users matching candidate's course/year
                if (strtolower(trim($position->name)) === 'representative') {
                    $groups = [];

                    foreach ($position->candidates as $candidate) {
                        $course = $candidate->course ?? 'Unknown';
                        $year = $candidate->year_level ?? 'Unknown';
                        $groupKey = $course . ' ' . $year;

                        $voteCount = Vote::where('position_id', $position->id)
                            ->where('election_id', $electionId)
                            ->where('candidate_id', $candidate->id)
                            ->whereHas('user', function($query) use ($candidate) {
                                $query->where('strand', $candidate->course)
                                      ->where('year', $candidate->year_level);
                            })
                            ->count();

                        if (!isset($groups[$groupKey])) {
                            $groups[$groupKey] = [
                                'course' => $course,
                                'year' => $year,
                                'candidates' => [],
                                'group_total_votes' => 0,
                                'abstain_votes' => 0,
                            ];
                        }

                        $groups[$groupKey]['candidates'][] = [
                            'id' => $candidate->id,
                            'name' => $candidate->full_name,
                            'party' => $candidate->party->name ?? 'No Party',
                            'votes' => $voteCount,
                        ];

                        $groups[$groupKey]['group_total_votes'] += $voteCount;
                        $totalVotes += $voteCount;
                    }

                    // compute percentages and rank for candidates in each group
                    foreach ($groups as $key => $group) {
                        usort($groups[$key]['candidates'], function($a, $b) {
                            return $b['votes'] - $a['votes'];
                        });

                        $rank = 1;
                        foreach ($groups[$key]['candidates'] as &$cand) {
                            $cand['percentage'] = $groups[$key]['group_total_votes'] > 0
                                ? round(($cand['votes'] / $groups[$key]['group_total_votes']) * 100, 2)
                                : 0;
                            $cand['rank'] = $rank++;
                        }
                        unset($cand);
                    }

                    $formattedResults[] = [
                        'position_id' => $position->id,
                        'position_name' => $position->name,
                        'total_votes' => $totalVotes,
                        'groups' => array_values($groups),
                    ];
                } else {
                    $candidateResults = [];
                    $totalVotes = 0;

                    foreach ($position->candidates as $candidate) {
                        // Fresh vote count from database
                        $voteCount = Vote::where('position_id', $position->id)
                            ->where('election_id', $electionId)
                            ->where('candidate_id', $candidate->id)
                            ->count();
                        
                        $totalVotes += $voteCount;
                        
                        $candidateResults[] = [
                            'candidate' => $candidate,
                            'votes' => $voteCount,
                        ];
                    }

                    // No abstain votes in database; skip abstain counting
                    $abstainVotes = 0;

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
            Log::error('AJAX results error: ' . $e->getMessage(), [
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

                // Representative: group by course/year
                if (strtolower(trim($position->name)) === 'representative') {
                    foreach ($position->candidates->groupBy(function($c) {
                        return ($c->course ?? 'Unknown') . ' ' . ($c->year_level ?? 'Unknown');
                    }) as $groupKey => $candidates) {
                        $first = $candidates->first();
                        $course = $first->course ?? 'Unknown';
                        $year = $first->year_level ?? 'Unknown';

                        fputcsv($file, ["Group: {$course} {$year}"]);
                        fputcsv($file, ['Candidate Name', 'Party', 'Votes', 'Percentage']);

                        $groupTotal = 0;
                        $rows = [];

                        foreach ($candidates as $candidate) {
                            $votes = Vote::where('position_id', $position->id)
                                ->where('election_id', $election->id)
                                ->where('candidate_id', $candidate->id)
                                ->whereHas('user', function($query) use ($candidate) {
                                    $query->where('strand', $candidate->course)
                                          ->where('year', $candidate->year_level);
                                })
                                ->count();

                            $rows[] = [
                                'name' => $candidate->full_name,
                                'party' => $candidate->party->name ?? 'No Party',
                                'votes' => $votes,
                            ];

                            $groupTotal += $votes;
                        }

                        // Write rows with percentages
                        usort($rows, function($a, $b) { return $b['votes'] - $a['votes']; });
                        foreach ($rows as $r) {
                            $percentage = $groupTotal > 0 ? round(($r['votes'] / $groupTotal) * 100, 2) : 0;
                            fputcsv($file, [$r['name'], $r['party'], $r['votes'], $percentage . '%']);
                        }

                        fputcsv($file, ['Group Total Votes', '', $groupTotal, '']);
                        fputcsv($file, []);
                    }
                } else {
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

                    // No abstain votes in database; skip abstain counting

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

                    fputcsv($file, ['Total Votes', '', $totalVotes, '100%']);
                    fputcsv($file, []); // Empty line between positions
                    }
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

            if (strtolower(trim($position->name)) === 'representative') {
                // group candidates by course/year
                $groups = [];
                foreach ($position->candidates as $candidate) {
                    $course = $candidate->course ?? 'Unknown';
                    $year = $candidate->year_level ?? 'Unknown';
                    $groupKey = $course . ' ' . $year;

                    if (!isset($groups[$groupKey])) {
                        $groups[$groupKey] = [
                            'course' => $course,
                            'year' => $year,
                            'candidates' => [],
                            'group_total_votes' => 0,
                            'abstain_votes' => 0,
                        ];
                    }

                    $votes = $candidate->votes()->where('position_id', $position->id)->count();
                    $groups[$groupKey]['candidates'][] = [
                        'candidate' => $candidate,
                        'votes' => $votes,
                    ];
                    $groups[$groupKey]['group_total_votes'] += $votes;
                    $totalVotes += $votes;
                }

                    foreach ($groups as $key => $group) {
                        $groups[$key]['candidates'] = collect($groups[$key]['candidates'])->sortByDesc('votes')->values()->all();
                    }

                $results[] = [
                    'position' => $position,
                    'groups' => $groups,
                    'total_votes' => $totalVotes,
                    'abstain_votes' => 0,
                ];
            } else {
                // Non-representative: collect candidate results and totals
                foreach ($position->candidates as $candidate) {
                    $voteCount = $candidate->votes()->where('position_id', $position->id)->count();
                    $totalVotes += $voteCount;
                    $candidateResults[] = [
                        'candidate' => $candidate,
                        'votes' => $voteCount,
                    ];
                }

                // Sort candidates by votes (descending)
                usort($candidateResults, function($a, $b) {
                    return $b['votes'] - $a['votes'];
                });

                $results[] = [
                    'position' => $position,
                    'candidates' => $candidateResults,
                    'total_votes' => $totalVotes,
                    'abstain_votes' => 0,
                ];
            }
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
