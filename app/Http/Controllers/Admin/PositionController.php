<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request)
    {
        $query = Position::with('election');

        // Search by position name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter by election
        if ($request->filled('election_id')) {
            $query->where('election_id', $request->election_id);
        }

        $positions = $query->latest()->get();
        $elections = Election::all();

        return view('admin.positions.index', compact('positions', 'elections'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        $elections = Election::all();

        return view('admin.positions.create', compact('elections'));
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'election_id' => 'required|exists:elections,id',
            'max_votes' => 'required|integer|min:1',
            'display_order' => 'nullable|integer|min:0',
        ]);

        Position::create($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position created successfully!');
    }

    /**
     * Display the specified position.
     */
    public function show(Position $position)
    {
        $position->load(['election', 'candidates.party']);

        return view('admin.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position)
    {
        $elections = Election::all();

        return view('admin.positions.edit', compact('position', 'elections'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'election_id' => 'required|exists:elections,id',
            'max_votes' => 'required|integer|min:1',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $position->update($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position updated successfully!');
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position)
    {
        // Check if position has candidates
        if ($position->candidates()->count() > 0) {
            return redirect()->route('admin.positions.index')
                ->with('error', 'Cannot delete position with existing candidates!');
        }

        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position deleted successfully!');
    }

    /**
     * Import positions from CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            // Get headers
            $headers = array_shift($csvData);

            // Validate headers
            $requiredHeaders = ['name', 'election_id', 'max_votes'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (! empty($missingHeaders)) {
                return redirect()->route('admin.positions.index')
                    ->with('error', 'CSV file is missing required columns: '.implode(', ', $missingHeaders));
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-based index

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Create associative array
                $data = array_combine($headers, $row);

                // Validate row data
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'election_id' => 'required|exists:elections,id',
                    'max_votes' => 'required|integer|min:1',
                    'display_order' => 'nullable|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $skippedCount++;
                    $errors[] = "Row {$rowNumber}: ".implode(', ', $validator->errors()->all());

                    continue;
                }

                // Create position
                Position::create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'election_id' => $data['election_id'],
                    'max_votes' => $data['max_votes'],
                    'display_order' => $data['display_order'] ?? 0,
                ]);

                $importedCount++;
            }

            $message = "Successfully imported {$importedCount} position(s).";
            if ($skippedCount > 0) {
                $message .= " Skipped {$skippedCount} row(s) due to validation errors.";
            }

            if (! empty($errors)) {
                session()->flash('import_errors', $errors);
            }

            return redirect()->route('admin.positions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.positions.index')
                ->with('error', 'Error importing CSV: '.$e->getMessage());
        }
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="positions_template.csv"',
        ];

        $columns = ['name', 'description', 'election_id', 'max_votes', 'display_order'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, $columns);

            // Add sample data
            fputcsv($file, [
                'President',
                'Chief executive officer of the student council',
                '1',
                '1',
                '1',
            ]);
            fputcsv($file, [
                'Vice President',
                'Second in command of the student council',
                '1',
                '1',
                '2',
            ]);
            fputcsv($file, [
                'Secretary',
                'Handles documentation and records',
                '1',
                '1',
                '3',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
