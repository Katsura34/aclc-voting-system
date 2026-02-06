<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Party;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get statistics with optimized queries
            $totalStudents = Student::count();
            $totalVoted = Student::where('has_voted', true)->count();
            $votingPercentage = $totalStudents > 0 ? round(($totalVoted / $totalStudents) * 100, 2) : 0;
            
            $activeElections = Election::where('is_active', true)->count();
            $totalCandidates = Candidate::count();
            $totalParties = Party::count();
            
            // Get active election details with caching
            $activeElection = Election::getActiveElection();
            
            // Get recent voters with optimized query
            $recentVoters = Student::where('has_voted', true)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get voting progress by position (if active election exists)
            $positionStats = [];
            if ($activeElection) {
                $positions = $activeElection->positions()
                    ->withCount('candidates')
                    ->get();
                
                foreach ($positions as $position) {
                    $positionStats[] = [
                        'name' => $position->name,
                        'candidates' => $position->candidates_count,
                        'max_winners' => $position->max_winners,
                    ];
                }
            }
            
            return view('admin.dashboard', compact(
                'totalStudents',
                'totalVoted',
                'votingPercentage',
                'activeElections',
                'totalCandidates',
                'totalParties',
                'activeElection',
                'recentVoters',
                'positionStats'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty data on error
            return view('admin.dashboard', [
                'totalStudents' => 0,
                'totalVoted' => 0,
                'votingPercentage' => 0,
                'activeElections' => 0,
                'totalCandidates' => 0,
                'totalParties' => 0,
                'activeElection' => null,
                'recentVoters' => collect(),
                'positionStats' => []
            ])->with('error', 'An error occurred while loading the dashboard. Some data may be missing.');
        }
    }
}
