<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    /**
     * Handle photo upload for a candidate.
     *
     * @param \Illuminate\Http\UploadedFile $photo
     * @return string The path where the photo was stored
     * @throws \Exception If upload or storage fails
     */
    private function handlePhotoUpload($photo)
    {
        // Basic upload checks
        if (!$photo || !$photo->isValid()) {
            throw new \Exception('Photo upload failed. Please ensure the file is a valid image and within the allowed size limit.');
        }

        // Allowed mime types and max size (2MB)
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];

        // Prefer the client-provided mime type; avoid using guessers requiring php_fileinfo
        try {
            $mime = $photo->getClientMimeType() ?: null;
        } catch (\Throwable $e) {
            $mime = null;
        }

        $size = null;
        try {
            $size = $photo->getSize();
        } catch (\Throwable $_) {
            // leave size null and skip strict size check if unavailable
        }

        if ($mime && !in_array($mime, $allowedMimes, true)) {
            throw new \Exception('Unsupported image type. Allowed types: JPG, JPEG, PNG. Detected: ' . ($mime ?: 'unknown'));
        }

        if ($size !== null && $size > 2 * 1024 * 1024) {
            throw new \Exception('Image exceeds maximum allowed size of 2MB.');
        }

        // Build a safe filename and choose extension from multiple fallbacks without relying on guessers
        $extension = null;
        try {
            $extension = $photo->getClientOriginalExtension();
        } catch (\Throwable $_) {
            $extension = null;
        }

        if (!$extension) {
            // Try extension from the original client filename
            try {
                $originalName = $photo->getClientOriginalName();
                $extFromName = pathinfo($originalName, PATHINFO_EXTENSION);
                if ($extFromName) {
                    $extension = $extFromName;
                }
            } catch (\Throwable $_) {
                // ignore
            }
        }

        if (!$extension) {
            if ($mime === 'image/png') {
                $extension = 'png';
            } else {
                $extension = 'jpg';
            }
        }

        $filename = uniqid('candidate_', true) . '.' . $extension;

        // Ensure candidates directory exists on the public disk
        try {
            if (!Storage::disk('public')->exists('candidates')) {
                Storage::disk('public')->makeDirectory('candidates');
                Log::info('Created "candidates" directory on public disk');
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure candidates directory exists', ['error' => $e->getMessage()]);
            // Proceed — storeAs may still work or will throw its own exception
        }

        // Attempt to store the uploaded file and provide detailed logs on failure
        try {
            $photoPath = $photo->storeAs('candidates', $filename, 'public');
        } catch (\Exception $e) {
            Log::error('Exception while storing candidate photo', [
                'message' => $e->getMessage(),
                'original_name' => $photo->getClientOriginalName(),
                'mime' => $mime,
                'size' => $size,
            ]);
            throw new \Exception('Unable to save photo. Storage exception: ' . $e->getMessage());
        }

        // Confirm file exists and log
        $exists = Storage::disk('public')->exists($photoPath);
        if (!$exists) {
            Log::error('Photo was stored but file not found in storage after storeAs', ['photo_path' => $photoPath]);
            throw new \Exception('Unable to verify saved photo. Please try again.');
        }

        Log::info('Candidate photo stored successfully', ['photo_path' => $photoPath, 'mime' => $mime, 'size' => $size]);

        return $photoPath;
    }

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
                'course' => 'nullable|string|max:255',
                'year_level' => 'nullable|integer|min:1|max:12',
                'bio' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            DB::beginTransaction();
            
            try {
                // Handle photo upload
                if ($request->hasFile('photo')) {
                    try {
                        $photo = $request->file('photo');
                        Log::info('Received photo upload in store()', ['original_name' => $photo->getClientOriginalName(), 'mime' => $photo->getClientMimeType(), 'size' => $photo->getSize()]);
                        $photoPath = $this->handlePhotoUpload($photo);
                        $validated['photo_path'] = $photoPath;
                        Log::info('After handlePhotoUpload in store()', ['photo_path' => $photoPath, 'exists' => Storage::disk('public')->exists($photoPath)]);
                    } catch (\Exception $e) {
                        Log::error('Photo upload error in store()', ['error' => $e->getMessage()]);
                        return redirect()->back()
                            ->withErrors(['photo' => $e->getMessage()])
                            ->withInput();
                    }
                }

                // Remove the photo file from validated data before creating
                // Only photo_path should be used for database inserts
                $createData = Arr::except($validated, ['photo']);

                Candidate::create($createData);
                
                DB::commit();

                Log::info('Candidate created', ['name' => $validated['first_name'] . ' ' . $validated['last_name']]);

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
            Log::error('Candidate creation error: ' . $e->getMessage(), [
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
                'course' => 'nullable|string|max:255',
                'year_level' => 'nullable|integer|min:1|max:12',
                'bio' => 'nullable|string',
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
                    try {
                        $photo = $request->file('photo');
                        Log::info('Received photo upload in update()', ['original_name' => $photo->getClientOriginalName(), 'mime' => $photo->getClientMimeType(), 'size' => $photo->getSize(), 'candidate_id' => $candidate->id]);
                        $photoPath = $this->handlePhotoUpload($photo);
                        $validated['photo_path'] = $photoPath;
                        Log::info('After handlePhotoUpload in update()', ['photo_path' => $photoPath, 'exists' => Storage::disk('public')->exists($photoPath), 'candidate_id' => $candidate->id]);

                        // Delete old photo
                        if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
                            Storage::disk('public')->delete($candidate->photo_path);
                            Log::info('Deleted old candidate photo', ['old_path' => $candidate->photo_path, 'candidate_id' => $candidate->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Photo upload error in update()', ['error' => $e->getMessage(), 'candidate_id' => $candidate->id]);
                        return redirect()->back()
                            ->withErrors(['photo' => $e->getMessage()])
                            ->withInput();
                    }
                }
                
                // Remove the remove_photo flag and photo file from validated data before updating
                // Only photo_path should be used for database updates
                $updateData = Arr::except($validated, ['remove_photo', 'photo']);
                
                $candidate->update($updateData);

                DB::commit();

                Log::info('Candidate updated', ['candidate_id' => $candidate->id]);

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
            Log::error('Candidate update error: ' . $e->getMessage(), [
                'candidate_id' => $candidate->id,
                'trace' => $e->getTraceAsString()
            ]);

            $msg = 'Failed to update candidate. ' . $e->getMessage();

            return redirect()->back()
                ->with('error', $msg)
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

                Log::info('Candidate deleted', ['candidate_id' => $candidate->id]);

                return redirect()->route('admin.candidates.index')
                    ->with('success', 'Candidate deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Candidate deletion error: ' . $e->getMessage(), [
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
                'party_id' => 'required|exists:parties,id',
                'election_id' => 'nullable|exists:elections,id',
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
            // Normalize headers for case-insensitive matching
            $headers = array_map(fn($header) => strtolower(trim($header)), $headers);
            
            // Validate headers
            $requiredHeaders = ['first_name', 'last_name', 'position_name'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->route('admin.candidates.index')
                    ->with('error', 'CSV file is missing required columns: ' . implode(', ', $missingHeaders));
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            $positionsQuery = Position::select('id', 'name');
            if ($request->filled('election_id')) {
                $positionsQuery->where('election_id', $request->election_id);
            }
            $positions = $positionsQuery->get();

            $positionNameCounts = [];
            $positionNameLookup = [];
            foreach ($positions as $position) {
                $key = strtolower($position->name);
                $positionNameCounts[$key] = ($positionNameCounts[$key] ?? 0) + 1;
                $positionNameLookup[$key][] = $position->name;
            }

            $duplicatePositionKeys = array_keys(array_filter($positionNameCounts, fn($count) => $count > 1));

            if (!empty($duplicatePositionKeys)) {
                $duplicatePositionNames = array_map(
                    fn($key) => implode(' / ', array_unique($positionNameLookup[$key] ?? [$key])),
                    $duplicatePositionKeys
                );
                return redirect()->route('admin.candidates.index')
                    ->with('error', 'Multiple positions share the same name: ' . implode(', ', $duplicatePositionNames) . '. Please ensure unique position names before importing.');
            }

            $positionMap = [];
            foreach ($positions as $position) {
                $positionMap[strtolower($position->name)] = $position->id;
            }

            if (empty($positionMap)) {
                return redirect()->route('admin.candidates.index')
                    ->with('error', 'No positions found. Please create positions before importing candidates.');
            }

            DB::beginTransaction();
            
            try {
                foreach ($csvData as $index => $row) {
                    $rowNumber = $index + 2; // +2 because of header and 0-based index
                    
                    $row = array_map('trim', $row);

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    if (count($row) !== count($headers)) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: Expected " . count($headers) . " columns, found " . count($row) . ".";
                        continue;
                    }

                    // Create associative array
                    $data = array_combine($headers, $row);
                    
                    // Validate row data
                    $validator = Validator::make($data, [
                        'first_name' => 'required|string|max:255',
                        'last_name' => 'required|string|max:255',
                        'middle_name' => 'nullable|string|max:255',
                        'course' => 'nullable|string|max:255',
                        'year_level' => 'nullable|integer|min:1|max:12',
                        'bio' => 'nullable|string',
                        'position_name' => 'required|string',
                    ]);

                    if ($validator->fails()) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    $positionKey = strtolower($data['position_name']);
                    if (!isset($positionMap[$positionKey])) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: Position \"{$data['position_name']}\" not found.";
                        continue;
                    }
                    $positionId = $positionMap[$positionKey];

                    // Create candidate
                    Candidate::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'middle_name' => $data['middle_name'] ?? null,
                        'course' => $data['course'] ?? null,
                        'year_level' => isset($data['year_level']) && $data['year_level'] !== '' ? (int)$data['year_level'] : null,
                        'bio' => $data['bio'] ?? null,
                        'position_id' => $positionId,
                        'party_id' => $request->party_id,
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

                Log::info('Candidates imported', [
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
            Log::error('Candidate import error: ' . $e->getMessage(), [
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

        $columns = ['first_name', 'last_name', 'middle_name', 'course', 'year_level', 'bio', 'position_name'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data
            fputcsv($file, [
                'Juan',
                'Dela Cruz',
                'Santos',
                'STEM',
                '11',
                'Experienced leader with a vision for change',
                'President'
            ]);
            fputcsv($file, [
                'Maria',
                'Garcia',
                'Lopez',
                'ABM',
                '12',
                'Dedicated to serving the student body',
                'President'
            ]);
            fputcsv($file, [
                'Jose',
                'Reyes',
                '',
                'HUMSS',
                '11',
                'Committed to transparency and accountability',
                'Vice President'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
