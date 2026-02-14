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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidatePhotoUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_updating_candidate_without_photo_change_preserves_existing_photo(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-TEST',
        ]);

        [$election, $position, $party, $candidate] = $this->createCandidateWithPhoto();
        $originalPhotoPath = $candidate->photo_path;

        // Update candidate without changing photo
        $response = $this->actingAs($admin)->put(route('admin.candidates.update', $candidate), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'bio' => 'Updated bio',
        ]);

        $response->assertRedirect(route('admin.candidates.index'));
        
        $candidate->refresh();
        
        // Photo should still be there
        $this->assertEquals($originalPhotoPath, $candidate->photo_path);
        $this->assertEquals('Updated', $candidate->first_name);
        $this->assertEquals('Name', $candidate->last_name);
        $this->assertEquals('Updated bio', $candidate->bio);
        
        // Original photo file should still exist
        Storage::disk('public')->assertExists($originalPhotoPath);
    }

    public function test_updating_candidate_with_new_photo_replaces_old_photo(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-TEST-2',
        ]);

        [$election, $position, $party, $candidate] = $this->createCandidateWithPhoto();
        $originalPhotoPath = $candidate->photo_path;

        // Update candidate with new photo
        $newPhoto = UploadedFile::fake()->image('new_photo.jpg');
        
        $response = $this->actingAs($admin)->put(route('admin.candidates.update', $candidate), [
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'position_id' => $position->id,
            'party_id' => $party->id,
            'photo' => $newPhoto,
        ]);

        $response->assertRedirect(route('admin.candidates.index'));
        
        $candidate->refresh();
        
        // Photo path should be different
        $this->assertNotEquals($originalPhotoPath, $candidate->photo_path);
        $this->assertNotNull($candidate->photo_path);
        
        // New photo should exist
        Storage::disk('public')->assertExists($candidate->photo_path);
        
        // Old photo should be deleted
        Storage::disk('public')->assertMissing($originalPhotoPath);
    }

    public function test_removing_candidate_photo_sets_photo_path_to_null(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN-TEST-3',
        ]);

        [$election, $position, $party, $candidate] = $this->createCandidateWithPhoto();
        $originalPhotoPath = $candidate->photo_path;

        // Remove candidate photo
        $response = $this->actingAs($admin)->put(route('admin.candidates.update', $candidate), [
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'position_id' => $position->id,
            'party_id' => $party->id,
            'remove_photo' => '1',
        ]);

        $response->assertRedirect(route('admin.candidates.index'));
        
        $candidate->refresh();
        
        // Photo path should be null
        $this->assertNull($candidate->photo_path);
        
        // Old photo should be deleted
        Storage::disk('public')->assertMissing($originalPhotoPath);
    }

    private function createCandidateWithPhoto(): array
    {
        $election = Election::create([
            'title' => 'Test Election',
            'description' => 'Test election description',
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
            'color' => '#FF0000',
        ]);

        // Create a fake photo
        $photo = UploadedFile::fake()->image('candidate.jpg');
        $photoPath = $photo->store('candidates', 'public');

        $candidate = Candidate::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'photo_path' => $photoPath,
        ]);

        return [$election, $position, $party, $candidate];
    }
}
