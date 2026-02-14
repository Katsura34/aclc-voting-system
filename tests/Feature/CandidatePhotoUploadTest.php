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

        // Create required records
        $party = Party::factory()->create();
        $election = Election::factory()->create();
        $position = Position::factory()->create(['election_id' => $election->id]);

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
