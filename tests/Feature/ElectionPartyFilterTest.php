<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Party;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionPartyFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_election_only_returns_candidates_from_selected_parties(): void
    {
        $election = Election::create([
            'title' => 'Test Election',
            'description' => 'Desc',
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDay(),
            'is_active' => true,
        ]);

        $position = Position::create([
            'name' => 'President',
            'description' => null,
            'max_votes' => 1,
            'display_order' => 0,
        ]);

        $allowedParty = Party::create([
            'name' => 'Alpha',
            'color' => '#000000',
            'acronym' => 'ALP',
        ]);

        $otherParty = Party::create([
            'name' => 'Beta',
            'color' => '#ffffff',
            'acronym' => 'BET',
        ]);

        $election->positions()->attach($position->id);
        $election->parties()->attach($allowedParty->id);

        $allowedCandidate = Candidate::create([
            'first_name' => 'Alice',
            'last_name' => 'Allowed',
            'position_id' => $position->id,
            'party_id' => $allowedParty->id,
        ]);

        Candidate::create([
            'first_name' => 'Bob',
            'last_name' => 'Blocked',
            'position_id' => $position->id,
            'party_id' => $otherParty->id,
        ]);

        $activeElection = Election::getActiveElection();
        $candidates = $activeElection->positions->first()->candidates;

        $this->assertCount(1, $candidates);
        $this->assertTrue($candidates->first()->is($allowedCandidate));
    }
}
