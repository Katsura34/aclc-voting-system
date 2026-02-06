<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use App\Models\Candidate;
use Carbon\Carbon;

class VotingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Election
        $election = Election::create([
            'title' => 'ACLC Student Council Election 2025',
            'description' => 'Annual election for ACLC Student Council officers and representatives.',
            'is_active' => true,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(7),
            'allow_abstain' => true,
            'show_live_results' => false,
        ]);

        // Create Positions
        $positions = [
            ['name' => 'President', 'max_winners' => 1, 'order' => 1],
            ['name' => 'Vice President', 'max_winners' => 1, 'order' => 2],
            ['name' => 'Secretary', 'max_winners' => 1, 'order' => 3],
            ['name' => 'Treasurer', 'max_winners' => 1, 'order' => 4],
            ['name' => 'Auditor', 'max_winners' => 1, 'order' => 5],
            ['name' => 'Public Relations Officer', 'max_winners' => 1, 'order' => 6],
            ['name' => 'Representative', 'max_winners' => 3, 'order' => 7],
        ];

        $createdPositions = [];
        foreach ($positions as $position) {
            $createdPositions[$position['name']] = Position::create([
                'name' => $position['name'],
                'max_votes' => $position['max_winners'],
                'display_order' => $position['order'],
            ]);
        }

        // Create Parties
        $parties = [
            [
                'name' => 'Unity Party',
                'acronym' => 'UP',
                'color' => '#003366',
                'description' => 'Building bridges, creating unity among all students.',
            ],
            [
                'name' => 'Progress Alliance',
                'acronym' => 'PA',
                'color' => '#CC0000',
                'description' => 'Moving forward together towards excellence.',
            ],
            [
                'name' => 'Student Voice',
                'acronym' => 'SV',
                'color' => '#00A651',
                'description' => 'Your voice, your choice, your future.',
            ],
        ];

        $createdParties = [];
        foreach ($parties as $party) {
            $createdParties[$party['name']] = Party::create($party);
        }

        // Create Candidates
        $candidates = [
            // President
            [
                'first_name' => 'Roberto',
                'last_name' => 'Santos',
                'position' => 'President',
                'party' => 'Unity Party',
                'course' => 'BS Computer Science',
                'year_level' => '4th Year',
                'bio' => 'Dedicated student leader with vision for inclusive campus development.',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Dela Cruz',
                'position' => 'President',
                'party' => 'Progress Alliance',
                'course' => 'BS Information Technology',
                'year_level' => '4th Year',
                'bio' => 'Passionate advocate for student welfare and academic excellence.',
            ],

            // Vice President
            [
                'first_name' => 'Antonio',
                'last_name' => 'Reyes',
                'position' => 'Vice President',
                'party' => 'Unity Party',
                'course' => 'BS Business Administration',
                'year_level' => '3rd Year',
                'bio' => 'Committed to supporting student initiatives and programs.',
            ],
            [
                'first_name' => 'Patricia',
                'last_name' => 'Garcia',
                'position' => 'Vice President',
                'party' => 'Progress Alliance',
                'course' => 'BS Accountancy',
                'year_level' => '3rd Year',
                'bio' => 'Experienced organizer with proven track record in student activities.',
            ],

            // Secretary
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Lopez',
                'position' => 'Secretary',
                'party' => 'Unity Party',
                'course' => 'BS Office Administration',
                'year_level' => '2nd Year',
                'bio' => 'Detail-oriented and organized, ready to document student achievements.',
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'position' => 'Secretary',
                'party' => 'Student Voice',
                'course' => 'BS Computer Science',
                'year_level' => '2nd Year',
                'bio' => 'Tech-savvy secretary bringing innovation to student documentation.',
            ],

            // Treasurer
            [
                'first_name' => 'Angela',
                'last_name' => 'Torres',
                'position' => 'Treasurer',
                'party' => 'Progress Alliance',
                'course' => 'BS Accountancy',
                'year_level' => '3rd Year',
                'bio' => 'Fiscally responsible with experience in financial management.',
            ],
            [
                'first_name' => 'Ricardo',
                'last_name' => 'Fernandez',
                'position' => 'Treasurer',
                'party' => 'Student Voice',
                'course' => 'BS Business Administration',
                'year_level' => '3rd Year',
                'bio' => 'Transparent and accountable financial steward.',
            ],

            // Auditor
            [
                'first_name' => 'Stephanie',
                'last_name' => 'Cruz',
                'position' => 'Auditor',
                'party' => 'Unity Party',
                'course' => 'BS Accountancy',
                'year_level' => '4th Year',
                'bio' => 'Ensuring transparency and accountability in student funds.',
            ],

            // PRO
            [
                'first_name' => 'Michael',
                'last_name' => 'Rivera',
                'position' => 'Public Relations Officer',
                'party' => 'Progress Alliance',
                'course' => 'BS Mass Communication',
                'year_level' => '3rd Year',
                'bio' => 'Creative communicator bridging students and administration.',
            ],
            [
                'first_name' => 'Kristine',
                'last_name' => 'Ramos',
                'position' => 'Public Relations Officer',
                'party' => 'Student Voice',
                'course' => 'BS Marketing',
                'year_level' => '2nd Year',
                'bio' => 'Dynamic PR specialist with fresh ideas for student engagement.',
            ],

            // Representatives
            [
                'first_name' => 'Daniel',
                'last_name' => 'Aquino',
                'position' => 'Representative',
                'party' => 'Unity Party',
                'course' => 'BS Information Technology',
                'year_level' => '2nd Year',
                'bio' => 'Voice of the IT students, tech advocate.',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Gonzales',
                'position' => 'Representative',
                'party' => 'Progress Alliance',
                'course' => 'BS Psychology',
                'year_level' => '2nd Year',
                'bio' => 'Advocate for student mental health and wellness.',
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Castro',
                'position' => 'Representative',
                'party' => 'Student Voice',
                'course' => 'BS Engineering',
                'year_level' => '3rd Year',
                'bio' => 'Engineering solutions for student concerns.',
            ],
            [
                'first_name' => 'Michelle',
                'last_name' => 'Villanueva',
                'position' => 'Representative',
                'party' => 'Unity Party',
                'course' => 'BS Nursing',
                'year_level' => '2nd Year',
                'bio' => 'Caring representative for health science students.',
            ],
            [
                'first_name' => 'Kevin',
                'last_name' => 'Bautista',
                'position' => 'Representative',
                'party' => null,
                'course' => 'BS Computer Science',
                'year_level' => '3rd Year',
                'bio' => 'Independent candidate focused on student innovation.',
            ],
        ];

        foreach ($candidates as $candidate) {
            Candidate::create([
                'election_id' => $election->id,
                'first_name' => $candidate['first_name'],
                'last_name' => $candidate['last_name'],
                'position_id' => $createdPositions[$candidate['position']]->id,
                'party_id' => $candidate['party'] ? $createdParties[$candidate['party']]->id : null,
                'course' => $candidate['course'],
                'year_level' => $candidate['year_level'],
                'bio' => $candidate['bio'],
                'photo_path' => null,
            ]);
        }
    }
}
