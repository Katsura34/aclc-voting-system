<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $storedPath;
    public string $token;
    public ?int $actorUserId;

    public function __construct(string $storedPath, string $token, ?int $actorUserId = null)
    {
        $this->storedPath = $storedPath;
        $this->token = $token;
        $this->actorUserId = $actorUserId;
    }

    public function handle(): void
    {
        $cacheKey = "import:{$this->token}";

        try {
            // Get absolute file path
            $fullPath = Storage::path($this->storedPath);

            $rows = array_map('str_getcsv', file($fullPath));
            $header = array_shift($rows);

            // Normalize header
            $header = array_map(fn ($c) => strtolower(trim($c)), $header ?? []);
            $expected = ['usn', 'lastname', 'firstname', 'strand', 'year', 'gender', 'password'];

            if ($header !== $expected) {
                Cache::put($cacheKey, [
                    'status'    => 'error',
                    'message'   => 'Invalid CSV header format.',
                    'total'     => 0,
                    'processed' => 0,
                    'imported'  => 0,
                    'errors'    => 1,
                ], now()->addHour());
                return;
            }

            // Count total rows (excluding empty)
            $total = count(array_filter($rows, fn ($r) => !empty(array_filter($r))));

            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status'    => 'running',
                'total'     => $total,
                'processed' => 0,
                'imported'  => 0,
                'errors'    => 0,
            ]), now()->addHour());

            DB::beginTransaction();

            $batch = [];
            $batchSize = 500;

            $processed = 0;
            $imported = 0;
            $errors = 0;

            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $processed++;

                if (count($row) !== 7) {
                    $errors++;
                    $this->tick($cacheKey, $total, $processed, $imported, $errors);
                    continue;
                }

                [$usn, $lastname, $firstname, $strand, $year, $gender, $password] = $row;

                $usn = trim($usn);
                $lastname = trim($lastname);
                $firstname = trim($firstname);
                $strand = trim($strand);
                $year = trim($year);
                $gender = trim($gender);
                $password = trim($password);

                if ($usn === '' || $lastname === '' || $firstname === '' || $password === '') {
                    $errors++;
                    $this->tick($cacheKey, $total, $processed, $imported, $errors);
                    continue;
                }

                $email = $usn . '@aclc.edu.ph';

                if (
                    User::where('usn', $usn)->exists() ||
                    User::where('email', $email)->exists()
                ) {
                    $errors++;
                    $this->tick($cacheKey, $total, $processed, $imported, $errors);
                    continue;
                }

                if ($gender && !in_array($gender, ['Male', 'Female', 'Other'], true)) {
                    $errors++;
                    $this->tick($cacheKey, $total, $processed, $imported, $errors);
                    continue;
                }

                $batch[] = [
                    'usn'        => $usn,
                    'lastname'   => $lastname,
                    'firstname'  => $firstname,
                    'strand'     => $strand ?: null,
                    'year'       => $year ?: null,
                    'gender'     => $gender ?: null,
                    'email'      => $email,
                    'password'   => Hash::make($password),
                    'user_type'  => 'student',
                    'has_voted'  => false,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ];

                if (count($batch) >= $batchSize) {
                    User::insert($batch);
                    $imported += count($batch);
                    $batch = [];
                }

                // Update progress after each row
                $this->tick($cacheKey, $total, $processed, $imported, $errors);
            }

            if (!empty($batch)) {
                User::insert($batch);
                $imported += count($batch);
            }

            DB::commit();

            Cache::put($cacheKey, [
                'status'    => 'done',
                'message'   => "Import completed: {$imported} users, {$errors} errors.",
                'total'     => $total,
                'processed' => $processed,
                'imported'  => $imported,
                'errors'    => $errors,
            ], now()->addHour());

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('ImportUsersJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($cacheKey, [
                'status'  => 'error',
                'message' => 'Import failed: ' . $e->getMessage(),
                'total'   => 0,
                'processed' => 0,
                'imported'  => 0,
                'errors'    => 1,
            ], now()->addHour());
        } finally {
            // Always delete uploaded CSV
            Storage::delete($this->storedPath);
        }
    }

    private function tick(string $key, int $total, int $processed, int $imported, int $errors): void
    {
        Cache::put($key, [
            'status'    => 'running',
            'message'   => null,
            'total'     => $total,
            'processed' => $processed,
            'imported'  => $imported,
            'errors'    => $errors,
        ], now()->addHour());
    }
}
