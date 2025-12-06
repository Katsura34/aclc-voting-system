<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    /**
     * Display a listing of the candidates.
     */
    public function index(Request $request)
    {
        $query = Candidate::with(['position.election', 'party']);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filter by election
        if ($request->filled('election_id')) {
            $query->whereHas('position', function($q) use ($request) {
                $q->where('election_id', $request->election_id);
            });
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        // Filter by party
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        $candidates = $query->latest()->get();
        
        // Get filter options
        $elections = Election::all();
        $positions = Position::with('election')->get();
        $parties = Party::all();
        
        return view('admin.candidates.index', compact('candidates', 'elections', 'positions', 'parties'));
    }

    /**
     * Show the form for creating a new candidate.
     */
    public function create()
    {
        $elections = Election::all();
        $positions = Position::with('election')->get();
        $parties = Party::all();
        
        return view('admin.candidates.create', compact('elections', 'positions', 'parties'));
    }

    /**
     * Store a newly created candidate in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'position_id' => 'required|exists:positions,id',
                'party_id' => 'required|exists:parties,id',
                'bio' => 'nullable|string',
                'platform' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            DB::beginTransaction();
            
            try {
                // Handle photo upload
                if ($request->hasFile('photo')) {
                    $photo = $request->file('photo');
                    $photoPath = $photo->store('candidates', 'public');
                    $validated['photo_path'] = $photoPath;
                }

                Candidate::create($validated);
                
                DB::commit();

                \Log::info('Candidate created', ['name' => $validated['first_name'] . ' ' . $validated['last_name']]);

                return redirect()->route('admin.candidates.index')
                    ->with('success', 'Candidate created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Delete uploaded photo if candidate creation failed
                if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Candidate creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create candidate. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified candidate.
     */
    public function show(Candidate $candidate)
    {
        $candidate->load(['position.election', 'party']);
        
        return view('admin.candidates.show', compact('candidate'));
    }

    /**
     * Show the form for editing the specified candidate.
     */
    public function edit(Candidate $candidate)
    {
        $elections = Election::all();
        $positions = Position::with('election')->get();
        $parties = Party::all();
        
        return view('admin.candidates.edit', compact('candidate', 'elections', 'positions', 'parties'));
    }

    /**
     * Update the specified candidate in storage.
     */
    public function update(Request $request, Candidate $candidate)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'position_id' => 'required|exists:positions,id',
                'party_id' => 'required|exists:parties,id',
                'bio' => 'nullable|string',
                'platform' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
                'remove_photo' => 'nullable|boolean',
            ]);

            DB::beginTransaction();
            
            try {
                // Handle photo removal
                if ($request->input('remove_photo')) {
                    if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
                        Storage::disk('public')->delete($candidate->photo_path);
                    }
                    $validated['photo_path'] = null;
                }
                // Handle photo upload
                elseif ($request->hasFile('photo')) {
                    $photo = $request->file('photo');
                    $photoPath = $photo->store('candidates', 'public');
                    $validated['photo_path'] = $photoPath;
                    
                    // Delete old photo
                    if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
                        Storage::disk('public')->delete($candidate->photo_path);
                    }
                }
                
                // Remove the remove_photo flag before updating
                $updateData = collect($validated)->except('remove_photo')->toArray();
                
                $candidate->update($updateData);
                
                DB::commit();

                \Log::info('Candidate updated', ['candidate_id' => $candidate->id]);

                return redirect()->route('admin.candidates.index')
                    ->with('success', 'Candidate updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Delete uploaded photo if update failed
                if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Candidate update error: ' . $e->getMessage(), [
                'candidate_id' => $candidate->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update candidate. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified candidate from storage.
     */
    public function destroy(Candidate $candidate)
    {
        try {
            DB::beginTransaction();
            
            try {
                // Check if candidate has votes
                if ($candidate->votes()->count() > 0) {
                    return redirect()->route('admin.candidates.index')
                        ->with('error', 'Cannot delete candidate with existing votes!');
                }

                // Delete candidate photo if exists
                if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
                    Storage::disk('public')->delete($candidate->photo_path);
                }

                $candidate->delete();
                
                DB::commit();

                \Log::info('Candidate deleted', ['candidate_id' => $candidate->id]);

                return redirect()->route('admin.candidates.index')
                    ->with('success', 'Candidate deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Candidate deletion error: ' . $e->getMessage(), [
                'candidate_id' => $candidate->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.candidates.index')
                ->with('error', 'Failed to delete candidate. Please try again.');
        }
    }

    /**
     * Import candidates from CSV file.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('csv_file');
            
            if (!$file->isValid()) {
                return redirect()->route('admin.candidates.index')
                    ->with('error', 'The uploaded file is invalid. Please try again.');
            }
            
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            if (empty($csvData)) {
                return redirect()->route('admin.candidates.index')
                    ->with('error', 'The CSV file is empty.');
            }
            
            // Get headers
            $headers = array_shift($csvData);
            
            // Validate headers
            $requiredHeaders = ['first_name', 'last_name', 'position_id', 'party_id'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->route('admin.candidates.index')
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
                        'first_name' => 'required|string|max:255',
                        'last_name' => 'required|string|max:255',
                        'middle_name' => 'nullable|string|max:255',
                        'bio' => 'nullable|string',
                        'position_id' => 'required|exists:positions,id',
                        'party_id' => 'required|exists:parties,id',
                    ]);

                    if ($validator->fails()) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Create candidate
                    Candidate::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'middle_name' => $data['middle_name'] ?? null,
                        'bio' => $data['bio'] ?? null,
                        'position_id' => $data['position_id'],
                        'party_id' => $data['party_id'],
                    ]);

                    $importedCount++;
                }
                
                DB::commit();

                $message = "Successfully imported {$importedCount} candidate(s).";
                if ($skippedCount > 0) {
                    $message .= " Skipped {$skippedCount} row(s) due to validation errors.";
                }

                if (!empty($errors)) {
                    session()->flash('import_errors', $errors);
                }

                \Log::info('Candidates imported', [
                    'imported' => $importedCount,
                    'skipped' => $skippedCount
                ]);

                return redirect()->route('admin.candidates.index')
                    ->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.candidates.index')
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Candidate import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.candidates.index')
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
            'Content-Disposition' => 'attachment; filename="candidates_template.csv"',
        ];

        $columns = ['first_name', 'last_name', 'middle_name', 'bio', 'position_id', 'party_id'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data
            fputcsv($file, [
                'Juan',
                'Dela Cruz',
                'Santos',
                'Experienced leader with a vision for change',
                '1',
                '1'
            ]);
            fputcsv($file, [
                'Maria',
                'Garcia',
                'Lopez',
                'Dedicated to serving the student body',
                '1',
                '2'
            ]);
            fputcsv($file, [
                'Jose',
                'Reyes',
                '',
                'Committed to transparency and accountability',
                '2',
                '1'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
