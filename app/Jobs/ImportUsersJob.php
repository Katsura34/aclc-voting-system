<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use SplFileObject;

class ImportUsersJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour

    /**
     * The unique job ID for tracking progress.
     */
    protected string $jobId;

    /**
     * The path to the CSV file.
     */
    protected string $csvPath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobId, string $csvPath)
    {
        $this->jobId = $jobId;
        $this->csvPath = $csvPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Initialize progress tracking in database
            $this->initializeProgress();

            // Stream CSV using SplFileObject (memory efficient)
            $file = new SplFileObject($this->csvPath, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

            // Read and validate header
            $header = $file->current();
            $file->next();
            
            // Normalize header
            $header = array_map(function($col) {
                return strtolower(trim($col));
            }, $header);

            // Validate header format
            $expectedHeader = ['usn', 'lastname', 'firstname', 'strand', 'year', 'gender', 'password'];
            if ($header !== $expectedHeader) {
                $this->failJob('Invalid CSV format. Expected columns: ' . implode(', ', $expectedHeader));
                return;
            }

            // Count total rows for progress tracking
            $totalRows = $this->countCsvRows($file);
            $this->updateProgress(['total_rows' => $totalRows]);

            // Reset file pointer after counting
            $file->rewind();
            $file->next(); // Skip header again

            // Preload all existing USNs and emails in one query (avoid N+1)
            $existingUsns = User::pluck('usn')->flip()->all();
            $existingEmails = User::pluck('email')->flip()->all();

            // Password hash cache to avoid re-hashing same passwords
            $passwordHashCache = [];

            $batch = [];
            $batchSize = 1000;
            $imported = 0;
            $errors = [];
            $lineNumber = 2; // Start at 2 (after header)

            // Process CSV line by line (streaming)
            while (!$file->eof()) {
                $row = $file->current();
                $file->next();

                // Skip empty rows
                if (empty($row) || empty(array_filter($row))) {
                    $lineNumber++;
                    continue;
                }

                // Validate row has correct number of columns
                if (count($row) !== 7) {
                    $errors[] = "Line {$lineNumber}: Invalid number of columns";
                    $lineNumber++;
                    continue;
                }

                list($usn, $lastname, $firstname, $strand, $year, $gender, $password) = $row;

                // Trim all values
                $usn = trim($usn);
                $lastname = trim($lastname);
                $firstname = trim($firstname);
                $strand = trim($strand);
                $year = trim($year);
                $gender = trim($gender);
                $password = trim($password);

                // Basic validation
                if (empty($usn) || empty($lastname) || empty($firstname) || empty($password)) {
                    $errors[] = "Line {$lineNumber}: USN, lastname, firstname, and password are required";
                    $lineNumber++;
                    continue;
                }

                // Generate email from USN
                $email = $usn . '@aclc.edu.ph';

                // Check if user already exists (using preloaded data)
                if (isset($existingUsns[$usn])) {
                    $errors[] = "Line {$lineNumber}: USN '{$usn}' already exists";
                    $lineNumber++;
                    continue;
                }

                if (isset($existingEmails[$email])) {
                    $errors[] = "Line {$lineNumber}: Email '{$email}' already exists";
                    $lineNumber++;
                    continue;
                }

                // Validate gender
                if (!empty($gender) && !in_array($gender, ['Male', 'Female', 'Other'])) {
                    $errors[] = "Line {$lineNumber}: Invalid gender value. Must be Male, Female, or Other";
                    $lineNumber++;
                    continue;
                }

                // Cache password hash (avoid re-hashing same password multiple times)
                if (!isset($passwordHashCache[$password])) {
                    $passwordHashCache[$password] = Hash::make($password);
                }
                $hashedPassword = $passwordHashCache[$password];

                // Add to batch
                $batch[] = [
                    'usn' => $usn,
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'strand' => $strand ?: null,
                    'year' => $year ?: null,
                    'gender' => $gender ?: null,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'user_type' => 'student',
                    'has_voted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Track in preloaded sets
                $existingUsns[$usn] = true;
                $existingEmails[$email] = true;

                $lineNumber++;

                // Insert batch when it reaches the batch size
                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch, $imported, $errors);
                    $imported += count($batch);
                    $batch = [];

                    // Update progress
                    $this->updateProgress([
                        'processed_rows' => $lineNumber - 2,
                        'imported_count' => $imported,
                        'error_count' => count($errors),
                        'errors' => json_encode(array_slice($errors, 0, 100)), // Store first 100 errors
                    ]);
                }
            }

            // Insert remaining batch
            if (!empty($batch)) {
                $this->insertBatch($batch, $imported, $errors);
                $imported += count($batch);
            }

            // Final progress update
            $this->completeJob($imported, $errors);

        } catch (\Exception $e) {
            $this->failJob($e->getMessage());
            throw $e;
        } finally {
            // Clean up the uploaded CSV file
            if (file_exists($this->csvPath)) {
                @unlink($this->csvPath);
            }
        }
    }

    /**
     * Count total rows in CSV file.
     */
    protected function countCsvRows(SplFileObject $file): int
    {
        $file->seek(PHP_INT_MAX);
        return $file->key() + 1 - 1; // Subtract header row
    }

    /**
     * Insert a batch of users into the database.
     */
    protected function insertBatch(array $batch, int &$imported, array &$errors): void
    {
        try {
            DB::beginTransaction();
            User::insert($batch);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // If batch insert fails, log the error
            $errors[] = "Batch insert failed: " . $e->getMessage();
        }
    }

    /**
     * Initialize progress tracking.
     */
    protected function initializeProgress(): void
    {
        DB::table('import_progress')->updateOrInsert(
            ['job_id' => $this->jobId],
            [
                'total_rows' => 0,
                'processed_rows' => 0,
                'imported_count' => 0,
                'error_count' => 0,
                'errors' => null,
                'status' => 'processing',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update progress in database.
     */
    protected function updateProgress(array $data): void
    {
        DB::table('import_progress')
            ->where('job_id', $this->jobId)
            ->update(array_merge($data, ['updated_at' => now()]));
    }

    /**
     * Mark job as completed.
     */
    protected function completeJob(int $imported, array $errors): void
    {
        DB::table('import_progress')
            ->where('job_id', $this->jobId)
            ->update([
                'imported_count' => $imported,
                'error_count' => count($errors),
                'errors' => json_encode(array_slice($errors, 0, 100)),
                'status' => 'completed',
                'updated_at' => now(),
            ]);
    }

    /**
     * Mark job as failed.
     */
    protected function failJob(string $errorMessage): void
    {
        DB::table('import_progress')
            ->where('job_id', $this->jobId)
            ->update([
                'status' => 'failed',
                'errors' => json_encode([$errorMessage]),
                'updated_at' => now(),
            ]);
    }
}
