<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Party;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CandidateImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_uses_position_name_and_selected_party(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-IMPORT',
        ]);

        [$election, $position, $party] = $this->createElectionPositionAndParty();

        $csvPath = $this->createCsvFile([
            ['first_name', 'last_name', 'position_name'],
            ['Jane', 'Doe', 'President'],
        ]);

        try {
            $response = $this->actingAs($admin)->post('/admin/candidates/import', [
                'csv_file' => new UploadedFile($csvPath, 'candidates.csv', null, null, true),
                'party_id' => $party->id,
            ]);

            $response->assertRedirect(route('admin.candidates.index'));
            $this->assertDatabaseHas('candidates', [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'position_id' => $position->id,
                'party_id' => $party->id,
            ]);
        } finally {
            $this->deleteCsvFile($csvPath);
        }
    }

    public function test_import_matches_position_name_case_insensitively(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-IMPORT-LOWER',
        ]);

        [$election, $position, $party] = $this->createElectionPositionAndParty();

        $csvPath = $this->createCsvFile([
            ['first_name', 'last_name', 'position_name'],
            ['John', 'Smith', 'president'],
        ]);

        try {
            $response = $this->actingAs($admin)->post('/admin/candidates/import', [
                'csv_file' => new UploadedFile($csvPath, 'candidates.csv', null, null, true),
                'party_id' => $party->id,
                'election_id' => $election->id,
            ]);

            $response->assertRedirect(route('admin.candidates.index'));
            $this->assertDatabaseHas('candidates', [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'position_id' => $position->id,
                'party_id' => $party->id,
            ]);
        } finally {
            $this->deleteCsvFile($csvPath);
        }
    }

    public function test_import_requires_party_selection(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-IMPORT-NO-PARTY',
        ]);

        [$election, $position] = $this->createElectionPositionAndParty(false);

        $csvPath = $this->createCsvFile([
            ['first_name', 'last_name', 'position_name'],
            ['Anna', 'Lee', 'President'],
        ]);

        try {
            $response = $this->actingAs($admin)->post('/admin/candidates/import', [
                'csv_file' => new UploadedFile($csvPath, 'candidates.csv', null, null, true),
                'election_id' => $election->id,
            ]);

            $response->assertSessionHasErrors('party_id');
            $this->assertDatabaseCount('candidates', 0);
        } finally {
            $this->deleteCsvFile($csvPath);
        }
    }

    private function createElectionPositionAndParty(bool $includeParty = true): array
    {
        $election = Election::create([
            'title' => 'General Election',
            'description' => 'Test election',
            'is_active' => true,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDay(),
            'allow_abstain' => true,
            'show_live_results' => false,
        ]);

        $position = Position::create([
            'election_id' => $election->id,
            'name' => 'President',
            'max_votes' => 1,
            'display_order' => 1,
        ]);

        $party = null;
        if ($includeParty) {
            $party = Party::create([
                'name' => 'Test Party',
                'acronym' => 'TP',
                'color' => '#000000',
            ]);
        }

        return $includeParty ? [$election, $position, $party] : [$election, $position];
    }

    private function createCsvFile(array $rows): string
    {
        $csvPath = tempnam(sys_get_temp_dir(), 'candidates');
        $file = fopen($csvPath, 'w');

        foreach ($rows as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return $csvPath;
    }

    private function deleteCsvFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
