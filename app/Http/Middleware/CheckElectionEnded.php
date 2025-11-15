<?php

namespace App\Http\Middleware;

use App\Models\Election;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckElectionEnded
{
    /**
     * Handle an incoming request.
     *
     * Check if election has ended before showing results.
     * Admins can always view results.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow admins to always view results
        if (auth()->check() && auth()->user()->user_type === 'admin') {
            return $next($request);
        }

        // Get the election being viewed
        $electionId = $request->input('election_id') ?? $request->route('election');
        
        if ($electionId) {
            $election = Election::find($electionId);
            
            if ($election) {
                // Check if election has ended and if live results are disabled
                if (!$election->show_live_results && $election->end_date && now()->lt($election->end_date)) {
                    return redirect()->back()
                        ->with('error', 'Results will be available after the election ends on ' . $election->end_date->format('F d, Y h:i A'));
                }
            }
        }

        return $next($request);
    }
}
