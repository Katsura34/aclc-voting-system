<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\VotingRecord;
use Illuminate\Http\Request;

class VotingRecordController extends Controller
{
    /**
     * Display voting records for manual counting backup.
     */
    public function index(Request $request)
    {
        $elections = Election::orderBy('created_at', 'desc')->get();
        
        $query = VotingRecord::with(['student', 'election']);

        // Filter by election
        if ($request->filled('election_id')) {
            $query->where('election_id', $request->election_id);
        }

        // Order by voted_at timestamp
        $query->orderBy('voted_at', 'desc');

        $records = $query->paginate(50);

        return view('admin.voting-records.index', compact('records', 'elections'));
    }

    /**
     * Export voting records to CSV for manual counting.
     */
    public function export(Request $request)
    {
        $query = VotingRecord::with(['student', 'election']);

        if ($request->filled('election_id')) {
            $query->where('election_id', $request->election_id);
            $election = Election::find($request->election_id);
            $filename = 'voting-records-' . ($election ? slug($election->title) : 'all') . '-' . date('Y-m-d') . '.csv';
        } else {
            $filename = 'voting-records-all-' . date('Y-m-d') . '.csv';
        }

        $records = $query->orderBy('voted_at')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['#', 'Election', 'Student USN', 'Student Name', 'Voted At', 'IP Address']);

            // Data rows
            $counter = 1;
            foreach ($records as $record) {
                fputcsv($file, [
                    $counter++,
                    $record->election->title,
                    $record->student->usn,
                    $record->student->name,
                    $record->voted_at->format('Y-m-d H:i:s'),
                    $record->ip_address ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
