<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Party;
use App\Models\Position;
use App\Models\Election;
use App\Models\Candidate;

class CandidatePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_png_when_updating_candidate()
    {
        Storage::fake('public');

        // Create required records without relying on model factories
        $party = Party::create([
            'name' => 'Test Party',
            'acronym' => 'TP',
            'color' => '#000000',
            'description' => 'Test party',
        ]);

        $election = Election::create([
            'title' => 'Test Election',
            'description' => 'Test election',
            'is_active' => false,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'allow_abstain' => true,
            'show_live_results' => false,
        ]);

        $position = Position::create([
            'election_id' => $election->id,
            'name' => 'President',
            'max_winners' => 1,
            'order' => 0,
        ]);

        $candidate = Candidate::create([
            'first_name' => 'Test',
            'last_name' => 'Candidate',
            'position_id' => $position->id,
            'party_id' => $party->id,
        ]);

        // Create an admin user and authenticate
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin);

        $file = UploadedFile::fake()->image('photo.png', 100, 100)->size(500);

        $response = $this->put(route('admin.candidates.update', $candidate), [
            'first_name' => 'Test',
            'last_name' => 'Candidate',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'photo' => $file,
        ]);

        $response->assertRedirect(route('admin.candidates.index'));

        $candidate->refresh();

        $this->assertNotNull($candidate->photo_path, 'photo_path should be set after upload');
        Storage::disk('public')->assertExists($candidate->photo_path);
    }
}
