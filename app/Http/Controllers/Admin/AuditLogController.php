<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs for an election.
     */
    public function index(Request $request)
    {
        try {
            $elections = Election::all();
            $selectedElection = null;
            $auditLogs = collect();
            $totalVotes = 0;

            if ($request->filled('election_id')) {
                $selectedElection = Election::find($request->election_id);

                if ($selectedElection) {
                    // Get audit logs with eager loading for better performance
                    $query = AuditLog::with(['user', 'position', 'candidate', 'election'])
                        ->where('election_id', $selectedElection->id)
                        ->orderBy('voted_at', 'desc');

                    // Apply filters if provided
                    if ($request->filled('position_id')) {
                        $query->where('position_id', $request->position_id);
                    }

                    if ($request->filled('search')) {
                        $search = $request->search;
                        $query->where(function ($q) use ($search) {
                            $q->where('user_usn', 'like', "%{$search}%")
                              ->orWhere('user_name', 'like', "%{$search}%")
                              ->orWhere('candidate_name', 'like', "%{$search}%");
                        });
                    }

                    $auditLogs = $query->paginate(50);
                    $totalVotes = AuditLog::where('election_id', $selectedElection->id)->count();
                }
            }

            return view('admin.audit-logs.index', compact(
                'elections',
                'selectedElection',
                'auditLogs',
                'totalVotes'
            ));
        } catch (\Exception $e) {
            \Log::error('Audit log display error: ' . $e->getMessage(), [
                'election_id' => $request->election_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to load audit logs. Please try again.');
        }
    }

    /**
     * Export audit logs as CSV.
     */
    public function export(Request $request)
    {
        $election = Election::find($request->election_id);

        if (!$election) {
            return redirect()->route('admin.audit-logs.index')
                ->with('error', 'Election not found!');
        }

        $filename = 'audit_log_' . str_replace(' ', '_', $election->title) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($election) {
            $file = fopen('php://output', 'w');

            // Add header
            fputcsv($file, ['Audit Log: ' . $election->title]);
            fputcsv($file, ['Generated on: ' . date('F d, Y h:i A')]);
            fputcsv($file, []); // Empty line

            // Column headers
            fputcsv($file, [
                'Timestamp',
                'Student USN',
                'Student Name',
                'Position',
                'Candidate',
                'IP Address'
            ]);

            // Get all audit logs for this election
            $auditLogs = AuditLog::where('election_id', $election->id)
                ->orderBy('voted_at', 'asc')
                ->get();

            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->voted_at->format('Y-m-d H:i:s'),
                    $log->user_usn,
                    $log->user_name,
                    $log->position_name,
                    $log->candidate_name,
                    $log->ip_address
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display printable audit log page.
     */
    public function print(Request $request)
    {
        $election = Election::find($request->election_id);

        if (!$election) {
            return redirect()->route('admin.audit-logs.index')
                ->with('error', 'Election not found!');
        }

        // Get audit logs grouped by position
        $auditLogs = AuditLog::with(['position', 'candidate'])
            ->where('election_id', $election->id)
            ->orderBy('position_id')
            ->orderBy('voted_at', 'asc')
            ->get()
            ->groupBy('position_id');

        $totalVoters = AuditLog::where('election_id', $election->id)
            ->distinct('user_id')
            ->count('user_id');

        return view('admin.audit-logs.print', compact(
            'election',
            'auditLogs',
            'totalVoters'
        ));
    }
}
