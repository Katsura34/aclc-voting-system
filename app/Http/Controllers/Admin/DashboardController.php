<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Party;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalStudents = User::where('user_type', 'student')->count();
        $totalVoted = User::where('user_type', 'student')->where('has_voted', true)->count();
        $votingPercentage = $totalStudents > 0 ? round(($totalVoted / $totalStudents) * 100, 2) : 0;
        
        $activeElections = Election::where('is_active', true)->count();
        $totalCandidates = Candidate::count();
        $totalParties = Party::count();
        
        // Get active election details
        $activeElection = Election::where('is_active', true)
            ->with(['positions.candidates.party'])
            ->first();
        
        // Get recent voters
        $recentVoters = User::where('user_type', 'student')
            ->where('has_voted', true)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        
        // Get voting progress by position (if active election exists)
        $positionStats = [];
        if ($activeElection) {
            $positions = Position::where('election_id', $activeElection->id)
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
    }
}
