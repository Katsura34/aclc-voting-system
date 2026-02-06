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

        $party = Party::create([
            'name' => 'Test Party',
            'acronym' => 'TP',
            'color' => '#000000',
        ]);

        $csvPath = tempnam(sys_get_temp_dir(), 'candidates');
        $file = fopen($csvPath, 'w');
        fputcsv($file, ['first_name', 'last_name', 'position_name']);
        fputcsv($file, ['Jane', 'Doe', 'President']);
        fclose($file);

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
            if (file_exists($csvPath)) {
                unlink($csvPath);
            }
        }
    }
}
