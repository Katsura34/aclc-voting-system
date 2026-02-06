<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Election;
use App\Models\Party;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_election_without_database_errors(): void
    {
        $admin = Admin::create([
            'username' => 'admin1',
            'name' => 'Admin One',
            'password' => 'password',
        ]);

        $election = Election::create([
            'title' => 'Original Election',
            'description' => 'Original description',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'is_active' => false,
        ]);

        $positionA = Position::create([
            'name' => 'President',
            'description' => 'Leads the council',
            'max_votes' => 1,
            'display_order' => 1,
        ]);

        $positionB = Position::create([
            'name' => 'Vice President',
            'description' => 'Assists the president',
            'max_votes' => 1,
            'display_order' => 2,
        ]);

        $partyA = Party::create([
            'name' => 'Unity Party',
            'acronym' => 'UP',
            'color' => '#000000',
        ]);

        $partyB = Party::create([
            'name' => 'Progressive Party',
            'acronym' => 'PP',
            'color' => '#ffffff',
        ]);

        $election->positions()->sync([
            $positionA->id => ['display_order' => 0],
            $positionB->id => ['display_order' => 1],
        ]);
        $election->parties()->sync([$partyA->id]);

        $newStart = Carbon::now()->addDays(3)->startOfMinute();
        $newEnd = Carbon::now()->addDays(4)->startOfMinute();

        $response = $this->actingAs($admin, 'admin')->put(
            route('admin.elections.update', $election),
            [
                'title' => 'Updated Election',
                'description' => 'Updated description',
                'start_date' => $newStart->toDateTimeLocalString(),
                'end_date' => $newEnd->toDateTimeLocalString(),
                'is_active' => true,
                'positions' => [$positionB->id, $positionA->id],
                'parties' => [$partyB->id],
            ]
        );

        $response->assertRedirect(route('admin.elections.index'));
        $response->assertSessionHas('success', 'Election updated successfully!');

        $freshElection = $election->fresh();

        $this->assertEquals('Updated Election', $freshElection->title);
        $this->assertTrue($freshElection->is_active);
        $this->assertEquals(
            [$positionB->id, $positionA->id],
            $freshElection->positions()->pluck('positions.id')->toArray()
        );
        $this->assertEquals([$partyB->id], $freshElection->parties()->pluck('parties.id')->toArray());
    }
}
