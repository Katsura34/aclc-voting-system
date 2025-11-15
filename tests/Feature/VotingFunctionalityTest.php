<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Party;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VotingFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test one-time voting enforcement.
     */
    public function test_one_time_voting_enforcement(): void
    {
        $user = User::factory()->create([
            'usn' => 'voter001',
            'password' => Hash::make('password'),
            'user_type' => 'student',
            'has_voted' => false,
        ]);

        $election = Election::factory()->create([
            'is_active' => true,
            'allow_abstain' => false,
        ]);

        $position = Position::factory()->create([
            'election_id' => $election->id,
        ]);

        $party = Party::factory()->create();

        $candidate = Candidate::factory()->create([
            'position_id' => $position->id,
            'party_id' => $party->id,
        ]);

        $this->actingAs($user);

        // First vote should succeed
        $response = $this->post(route('voting.submit'), [
            "position_{$position->id}" => $candidate->id,
        ]);

        $response->assertRedirect(route('voting.success'));
        $this->assertTrue($user->fresh()->has_voted);

        // Second vote attempt should redirect to success page
        $response = $this->post(route('voting.submit'), [
            "position_{$position->id}" => $candidate->id,
        ]);

        $response->assertRedirect(route('voting.success'));
        $response->assertSessionHas('error', 'You have already voted!');
    }

    /**
     * Test abstain voting functionality.
     */
    public function test_abstain_voting(): void
    {
        $user = User::factory()->create([
            'user_type' => 'student',
            'has_voted' => false,
        ]);

        $election = Election::factory()->create([
            'is_active' => true,
            'allow_abstain' => true,
        ]);

        $position = Position::factory()->create([
            'election_id' => $election->id,
        ]);

        $this->actingAs($user);

        // Submit vote without selecting a candidate (abstain)
        $response = $this->post(route('voting.submit'), []);

        $response->assertRedirect(route('voting.success'));
        
        // Verify audit log for abstain
        $this->assertDatabaseHas('vote_audit_logs', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => null,
            'action' => 'vote_abstain',
        ]);
    }

    /**
     * Test vote audit logging.
     */
    public function test_vote_audit_logging(): void
    {
        $user = User::factory()->create([
            'user_type' => 'student',
            'has_voted' => false,
        ]);

        $election = Election::factory()->create([
            'is_active' => true,
        ]);

        $position = Position::factory()->create([
            'election_id' => $election->id,
        ]);

        $party = Party::factory()->create();

        $candidate = Candidate::factory()->create([
            'position_id' => $position->id,
            'party_id' => $party->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('voting.submit'), [
            "position_{$position->id}" => $candidate->id,
        ]);

        // Verify audit log was created
        $this->assertDatabaseHas('vote_audit_logs', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
            'action' => 'vote_cast',
        ]);

        $auditLog = VoteAuditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($auditLog->ip_address);
        $this->assertNotNull($auditLog->voted_at);
    }

    /**
     * Test vote reset functionality.
     */
    public function test_vote_reset_functionality(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $student = User::factory()->create([
            'user_type' => 'student',
            'has_voted' => true,
        ]);

        $election = Election::factory()->create();
        $position = Position::factory()->create(['election_id' => $election->id]);
        $party = Party::factory()->create();
        $candidate = Candidate::factory()->create([
            'position_id' => $position->id,
            'party_id' => $party->id,
        ]);

        Vote::create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($admin);

        // Reset vote
        $response = $this->patch(route('admin.users.reset-vote', $student));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($student->fresh()->has_voted);
        $this->assertDatabaseMissing('votes', [
            'user_id' => $student->id,
        ]);

        // Verify audit log for reset
        $this->assertDatabaseHas('vote_audit_logs', [
            'user_id' => $student->id,
            'action' => 'vote_reset_by_admin',
        ]);
    }

    /**
     * Test reset all votes functionality.
     */
    public function test_reset_all_votes(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        $students = User::factory()->count(3)->create([
            'user_type' => 'student',
            'has_voted' => true,
        ]);

        $election = Election::factory()->create();
        $position = Position::factory()->create(['election_id' => $election->id]);
        $party = Party::factory()->create();
        $candidate = Candidate::factory()->create([
            'position_id' => $position->id,
            'party_id' => $party->id,
        ]);

        foreach ($students as $student) {
            Vote::create([
                'user_id' => $student->id,
                'election_id' => $election->id,
                'position_id' => $position->id,
                'candidate_id' => $candidate->id,
            ]);
        }

        $this->actingAs($admin);

        $response = $this->post(route('admin.users.reset-all-votes'));

        $response->assertRedirect(route('admin.users.index'));
        
        foreach ($students as $student) {
            $this->assertFalse($student->fresh()->has_voted);
        }

        $this->assertEquals(0, Vote::count());
    }

    /**
     * Test results restriction until election ends.
     */
    public function test_results_restricted_until_election_ends(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $election = Election::factory()->create([
            'is_active' => true,
            'show_live_results' => false,
            'end_date' => now()->addDays(1), // Election not ended yet
        ]);

        $this->actingAs($student);

        $response = $this->get(route('admin.results.index', ['election_id' => $election->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test admin can view results anytime.
     */
    public function test_admin_can_view_results_anytime(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $election = Election::factory()->create([
            'is_active' => true,
            'show_live_results' => false,
            'end_date' => now()->addDays(1), // Election not ended yet
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.results.index', ['election_id' => $election->id]));

        $response->assertStatus(200);
    }
}
