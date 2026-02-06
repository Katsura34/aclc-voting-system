<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request)
    {
        $query = Position::with('candidates');

        // Search by position name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $positions = $query->latest()->get();
        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        return view('admin.positions.create');
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'max_votes' => 'required|integer|min:1',
                'display_order' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();
            
            try {
                Position::create($validated);
                
                DB::commit();

                \Log::info('Position created', ['name' => $validated['name']]);

                return redirect()->route('admin.positions.index')
                    ->with('success', 'Position created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Position creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create position. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified position.
     */
    public function show(Position $position)
    {
        $position->load(['candidates.party']);
        return view('admin.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'max_votes' => 'required|integer|min:1',
                'display_order' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();
            
            try {
                $position->update($validated);
                
                DB::commit();

                \Log::info('Position updated', ['position_id' => $position->id]);

                return redirect()->route('admin.positions.index')
                    ->with('success', 'Position updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Position update error: ' . $e->getMessage(), [
                'position_id' => $position->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update position. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position)
    {
        try {
            DB::beginTransaction();
            
            try {
                // Check if position has candidates
                if ($position->candidates()->count() > 0) {
                    return redirect()->route('admin.positions.index')
                        ->with('error', 'Cannot delete position with existing candidates!');
                }

                $position->delete();
                
                DB::commit();

                \Log::info('Position deleted', ['position_id' => $position->id]);

                return redirect()->route('admin.positions.index')
                    ->with('success', 'Position deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Position deletion error: ' . $e->getMessage(), [
                'position_id' => $position->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.positions.index')
                ->with('error', 'Failed to delete position. Please try again.');
        }
    }

    /**
     * Import positions from CSV file.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('csv_file');
            
            if (!$file->isValid()) {
                return redirect()->route('admin.positions.index')
                    ->with('error', 'The uploaded file is invalid. Please try again.');
            }
            
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            if (empty($csvData)) {
                return redirect()->route('admin.positions.index')
                    ->with('error', 'The CSV file is empty.');
            }
            
            // Get headers
            $headers = array_shift($csvData);
            
            // Validate headers
            $requiredHeaders = ['name', 'max_votes'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->route('admin.positions.index')
                    ->with('error', 'CSV file is missing required columns: ' . implode(', ', $missingHeaders));
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            DB::beginTransaction();
            
            try {
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
                        'max_votes' => 'required|integer|min:1',
                        'display_order' => 'nullable|integer|min:0',
                    ]);

                    if ($validator->fails()) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Create position
                    Position::create([
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'max_votes' => $data['max_votes'],
                        'display_order' => $data['display_order'] ?? 0,
                    ]);

                    $importedCount++;
                }
                
                DB::commit();

                $message = "Successfully imported {$importedCount} position(s).";
                if ($skippedCount > 0) {
                    $message .= " Skipped {$skippedCount} row(s) due to validation errors.";
                }

                if (!empty($errors)) {
                    session()->flash('import_errors', $errors);
                }

                \Log::info('Positions imported', [
                    'imported' => $importedCount,
                    'skipped' => $skippedCount
                ]);

                return redirect()->route('admin.positions.index')
                    ->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.positions.index')
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Position import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.positions.index')
                ->with('error', 'Error importing CSV: ' . $e->getMessage());
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

        $columns = ['name', 'description', 'max_votes', 'display_order'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data
            fputcsv($file, [
                'President',
                'Chief executive officer of the student council',
                '1',
                '1'
            ]);
            fputcsv($file, [
                'Vice President',
                'Second in command of the student council',
                '1',
                '2'
            ]);
            fputcsv($file, [
                'Secretary',
                'Handles documentation and records',
                '1',
                '3'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
