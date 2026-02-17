<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Party;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepresentativeResultsGroupingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that representative results are grouped by course and year level.
     */
    public function test_representative_results_are_grouped_by_course_and_year(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN001',
        ]);

        // Create election
        $election = Election::create([
            'title' => 'Test Election',
            'description' => 'Test Description',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ]);

        // Create party
        $party = Party::create([
            'name' => 'Test Party',
            'acronym' => 'TP',
            'color' => '#ff0000',
        ]);

        // Create representative position
        $position = Position::create([
            'name' => 'Representative',
            'description' => 'Student Representative',
            'max_votes' => 1,
            'display_order' => 1,
            'election_id' => $election->id,
        ]);

        // Create candidates for different courses and years
        $candidate1 = Candidate::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSCS',
            'year_level' => 1,
        ]);

        $candidate2 = Candidate::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSCS',
            'year_level' => 1,
        ]);

        $candidate3 = Candidate::create([
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSIT',
            'year_level' => 2,
        ]);

        // Create voters from different courses/years
        $voter1 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-001-BS',
            'strand' => 'BSCS',
            'year' => 1,
            'has_voted' => true,
        ]);

        $voter2 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-002-BS',
            'strand' => 'BSCS',
            'year' => 1,
            'has_voted' => true,
        ]);

        $voter3 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-003-BS',
            'strand' => 'BSIT',
            'year' => 2,
            'has_voted' => true,
        ]);

        // Create votes
        Vote::create([
            'user_id' => $voter1->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate1->id,
        ]);

        Vote::create([
            'user_id' => $voter2->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate2->id,
        ]);

        Vote::create([
            'user_id' => $voter3->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate3->id,
        ]);

        // Make request
        $response = $this->actingAs($admin)->get('/admin/results?election_id=' . $election->id);

        $response->assertStatus(200);

        // Check that results are grouped
        $results = $response->viewData('results');
        $this->assertNotEmpty($results);
        
        $representativeResult = collect($results)->firstWhere('position.name', 'Representative');
        $this->assertNotNull($representativeResult);
        $this->assertTrue($representativeResult['is_representative']);
        
        // Check that we have group-specific data
        $this->assertArrayHasKey('group_candidate_results', $representativeResult);
        $this->assertArrayHasKey('group_total_votes', $representativeResult);
        $this->assertArrayHasKey('group_abstain_votes', $representativeResult);
        
        // Check that we have 2 groups (BSCS Year 1 and BSIT Year 2)
        $this->assertCount(2, $representativeResult['group_candidate_results']);
        
        // Verify group keys
        $groupKeys = array_keys($representativeResult['group_candidate_results']);
        $this->assertContains('BSCS|1', $groupKeys);
        $this->assertContains('BSIT|2', $groupKeys);
        
        // Check vote counts for BSCS Year 1 group
        $bscsGroup = $representativeResult['group_candidate_results']['BSCS|1'];
        $this->assertCount(2, $bscsGroup); // Should have 2 candidates
        $this->assertEquals(2, $representativeResult['group_total_votes']['BSCS|1']); // 2 votes total
        
        // Check vote counts for BSIT Year 2 group
        $bsitGroup = $representativeResult['group_candidate_results']['BSIT|2'];
        $this->assertCount(1, $bsitGroup); // Should have 1 candidate
        $this->assertEquals(1, $representativeResult['group_total_votes']['BSIT|2']); // 1 vote total
    }

    /**
     * Test that abstain votes are counted per group.
     */
    public function test_abstain_votes_are_grouped_per_course_and_year(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN002',
        ]);

        // Create election
        $election = Election::create([
            'title' => 'Test Election 2',
            'description' => 'Test Description',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ]);

        // Create party
        $party = Party::create([
            'name' => 'Test Party 2',
            'acronym' => 'TP2',
            'color' => '#0000ff',
        ]);

        // Create representative position
        $position = Position::create([
            'name' => 'Representative',
            'description' => 'Student Representative',
            'max_votes' => 1,
            'display_order' => 1,
            'election_id' => $election->id,
        ]);

        // Create candidates for different courses and years
        $candidate1 = Candidate::create([
            'first_name' => 'Alice',
            'last_name' => 'Wonder',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSCS',
            'year_level' => 1,
        ]);

        $candidate2 = Candidate::create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSIT',
            'year_level' => 2,
        ]);

        // Create voters from different courses/years
        $voter1 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-010-BS',
            'strand' => 'BSCS',
            'year' => 1,
            'has_voted' => true,
        ]);

        $voter2 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-011-BS',
            'strand' => 'BSCS',
            'year' => 1,
            'has_voted' => true,
        ]);

        $voter3 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-012-BS',
            'strand' => 'BSIT',
            'year' => 2,
            'has_voted' => true,
        ]);

        // Create votes - voter1 votes for candidate, voter2 and voter3 abstain
        Vote::create([
            'user_id' => $voter1->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate1->id,
        ]);

        Vote::create([
            'user_id' => $voter2->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => null, // Abstain
        ]);

        Vote::create([
            'user_id' => $voter3->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => null, // Abstain
        ]);

        // Make request
        $response = $this->actingAs($admin)->get('/admin/results?election_id=' . $election->id);

        $response->assertStatus(200);

        // Check that abstain votes are grouped correctly
        $results = $response->viewData('results');
        $representativeResult = collect($results)->firstWhere('position.name', 'Representative');
        
        // BSCS Year 1 should have 1 abstain vote (voter2)
        $this->assertEquals(1, $representativeResult['group_abstain_votes']['BSCS|1']);
        $this->assertEquals(2, $representativeResult['group_total_votes']['BSCS|1']); // 1 vote + 1 abstain
        
        // BSIT Year 2 should have 1 abstain vote (voter3)
        $this->assertEquals(1, $representativeResult['group_abstain_votes']['BSIT|2']);
        $this->assertEquals(1, $representativeResult['group_total_votes']['BSIT|2']); // 1 abstain only
    }

    /**
     * Test that non-representative positions still work correctly.
     */
    public function test_non_representative_positions_work_normally(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN003',
        ]);

        // Create election
        $election = Election::create([
            'title' => 'Test Election 3',
            'description' => 'Test Description',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ]);

        // Create party
        $party = Party::create([
            'name' => 'Test Party 3',
            'acronym' => 'TP3',
            'color' => '#00ff00',
        ]);

        // Create president position (non-representative)
        $position = Position::create([
            'name' => 'President',
            'description' => 'Student President',
            'max_votes' => 1,
            'display_order' => 1,
            'election_id' => $election->id,
        ]);

        // Create candidates
        $candidate1 = Candidate::create([
            'first_name' => 'President',
            'last_name' => 'One',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSCS',
            'year_level' => 4,
        ]);

        $candidate2 = Candidate::create([
            'first_name' => 'President',
            'last_name' => 'Two',
            'position_id' => $position->id,
            'party_id' => $party->id,
            'course' => 'BSIT',
            'year_level' => 4,
        ]);

        // Create voters
        $voter1 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-020-BS',
            'strand' => 'BSCS',
            'year' => 1,
            'has_voted' => true,
        ]);

        $voter2 = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-021-BS',
            'strand' => 'BSIT',
            'year' => 2,
            'has_voted' => true,
        ]);

        // Create votes
        Vote::create([
            'user_id' => $voter1->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate1->id,
        ]);

        Vote::create([
            'user_id' => $voter2->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate2->id,
        ]);

        // Make request
        $response = $this->actingAs($admin)->get('/admin/results?election_id=' . $election->id);

        $response->assertStatus(200);

        // Check that non-representative position uses old structure
        $results = $response->viewData('results');
        $presidentResult = collect($results)->firstWhere('position.name', 'President');
        
        $this->assertNotNull($presidentResult);
        $this->assertFalse($presidentResult['is_representative']);
        $this->assertArrayHasKey('candidates', $presidentResult);
        $this->assertArrayHasKey('total_votes', $presidentResult);
        $this->assertArrayHasKey('abstain_votes', $presidentResult);
        
        // Check that all votes are counted (regardless of voter's course/year)
        $this->assertEquals(2, $presidentResult['total_votes']);
    }
}
