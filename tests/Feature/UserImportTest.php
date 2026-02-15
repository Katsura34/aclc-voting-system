<?php

namespace Tests\Feature;

use App\Jobs\ImportUsersJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for testing
        $this->admin = User::create([
            'usn' => 'ADMIN001',
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@aclc.edu.ph',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
            'has_voted' => false,
        ]);
    }

    /** @test */
    public function it_validates_csv_file_upload()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.import'), []);

        $response->assertSessionHasErrors('csv_file');
    }

    /** @test */
    public function it_dispatches_import_job_when_csv_is_uploaded()
    {
        Queue::fake();

        $this->actingAs($this->admin);

        // Create a valid CSV file
        $csv = "usn,lastname,firstname,strand,year,gender,password\n";
        $csv .= "2024-001,Doe,John,STEM,1,Male,password123\n";
        $csv .= "2024-002,Smith,Jane,ABM,2,Female,password123\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $response = $this->post(route('admin.users.import'), [
            'csv_file' => $file,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        
        // Assert job was dispatched
        Queue::assertPushed(ImportUsersJob::class);
    }

    /** @test */
    public function import_job_processes_valid_csv_data()
    {
        // Create a temporary CSV file
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        $csvContent .= "2024-001,Doe,John,STEM,1,Male,password123\n";
        $csvContent .= "2024-002,Smith,Jane,ABM,2,Female,password456\n";
        $csvContent .= "2024-003,Johnson,Bob,HUMSS,3,Male,password123\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_123';

        // Run the job
        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // Assert users were created
        $this->assertEquals(3, User::where('user_type', 'student')->count());

        // Verify specific users
        $this->assertDatabaseHas('users', [
            'usn' => '2024-001',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => '2024-001@aclc.edu.ph',
        ]);

        $this->assertDatabaseHas('users', [
            'usn' => '2024-002',
            'firstname' => 'Jane',
            'lastname' => 'Smith',
        ]);

        // Verify progress tracking
        $progress = DB::table('import_progress')->where('job_id', $jobId)->first();
        $this->assertEquals('completed', $progress->status);
        $this->assertEquals(3, $progress->total_rows);
        $this->assertEquals(3, $progress->imported_count);
    }

    /** @test */
    public function import_job_handles_duplicate_usn()
    {
        // Create an existing user
        User::create([
            'usn' => '2024-001',
            'firstname' => 'Existing',
            'lastname' => 'User',
            'email' => '2024-001@aclc.edu.ph',
            'password' => bcrypt('password'),
            'user_type' => 'student',
            'has_voted' => false,
        ]);

        // Create CSV with duplicate USN
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        $csvContent .= "2024-001,Doe,John,STEM,1st Year,Male,password123\n";
        $csvContent .= "2024-002,Smith,Jane,ABM,2nd Year,Female,password456\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_duplicate';

        // Run the job
        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // Assert only the second user was imported
        $this->assertEquals(2, User::where('user_type', 'student')->count());

        // Verify error was logged
        $progress = DB::table('import_progress')->where('job_id', $jobId)->first();
        $errors = json_decode($progress->errors, true);
        $this->assertGreaterThanOrEqual(1, count($errors));
        
        // Check if any error contains "already exists"
        $foundError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'already exists') !== false) {
                $foundError = true;
                break;
            }
        }
        $this->assertTrue($foundError, 'Should have at least one "already exists" error. Errors: ' . json_encode($errors));
    }

    /** @test */
    public function import_job_validates_gender_values()
    {
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        $csvContent .= "2024-001,Doe,John,STEM,1,InvalidGender,password123\n";
        $csvContent .= "2024-002,Smith,Jane,ABM,2,Female,password456\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_gender';

        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // Only valid user should be imported
        $this->assertEquals(1, User::where('user_type', 'student')->count());
        $this->assertDatabaseHas('users', ['usn' => '2024-002']);
        $this->assertDatabaseMissing('users', ['usn' => '2024-001']);
    }

    /** @test */
    public function import_job_requires_mandatory_fields()
    {
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        $csvContent .= ",Doe,John,STEM,1,Male,password123\n"; // Missing USN
        $csvContent .= "2024-002,,Jane,ABM,2,Female,password456\n"; // Missing lastname
        $csvContent .= "2024-003,Johnson,,HUMSS,3,Male,password789\n"; // Missing firstname
        $csvContent .= "2024-004,Brown,Bob,STEM,1,Male,\n"; // Missing password

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_required';

        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // No users should be imported
        $this->assertEquals(0, User::where('user_type', 'student')->count());

        // Verify errors were logged (at least 4 - one for each required field)
        $progress = DB::table('import_progress')->where('job_id', $jobId)->first();
        $this->assertGreaterThanOrEqual(4, $progress->error_count);
    }

    /** @test */
    public function import_progress_endpoint_returns_progress_data()
    {
        $this->actingAs($this->admin);

        // Create progress record
        $jobId = 'test_job_progress';
        DB::table('import_progress')->insert([
            'job_id' => $jobId,
            'total_rows' => 1000,
            'processed_rows' => 500,
            'imported_count' => 480,
            'error_count' => 20,
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.users.import-progress', ['jobId' => $jobId]));

        $response->assertOk();
        $response->assertJson([
            'status' => 'processing',
            'percentage' => 50.0,
            'total_rows' => 1000,
            'processed_rows' => 500,
            'imported_count' => 480,
            'error_count' => 20,
        ]);
    }

    /** @test */
    public function import_job_caches_password_hashes()
    {
        // Create CSV with repeated passwords
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        for ($i = 1; $i <= 10; $i++) {
            $csvContent .= "2024-" . str_pad($i, 3, '0', STR_PAD_LEFT) . ",Doe{$i},John{$i},STEM,1,Male,commonpass\n";
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_hash_cache';

        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // All 10 users should be imported
        $this->assertEquals(10, User::where('user_type', 'student')->count());

        // All should have the same password hash (due to caching)
        $users = User::where('user_type', 'student')->get();
        $firstHash = $users->first()->password;
        
        foreach ($users as $user) {
            $this->assertEquals($firstHash, $user->password);
        }
    }

    /** @test */
    public function import_job_processes_large_batch()
    {
        // Create CSV with 2500 rows (to test batch processing at 1000 rows)
        $csvContent = "usn,lastname,firstname,strand,year,gender,password\n";
        for ($i = 1; $i <= 2500; $i++) {
            $usn = '2024-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $csvContent .= "{$usn},Lastname{$i},Firstname{$i},STEM,1,Male,password123\n";
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_large_');
        file_put_contents($tempFile, $csvContent);

        $jobId = 'test_job_large';

        $job = new ImportUsersJob($jobId, $tempFile);
        $job->handle();

        // All users should be imported
        $this->assertEquals(2500, User::where('user_type', 'student')->count());

        // Verify progress
        $progress = DB::table('import_progress')->where('job_id', $jobId)->first();
        $this->assertEquals('completed', $progress->status);
        $this->assertEquals(2500, $progress->imported_count);
    }
}
