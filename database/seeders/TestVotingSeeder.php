<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use App\Models\Candidate;

class TestVotingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'usn' => 'admin001',
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@aclc.edu',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'has_voted' => false,
        ]);

        // Create test students
        $students = [
            ['usn' => 'STEM11-001', 'firstname' => 'John', 'lastname' => 'Doe', 'strand' => 'STEM', 'year' => 11],
            ['usn' => 'STEM11-002', 'firstname' => 'Jane', 'lastname' => 'Smith', 'strand' => 'STEM', 'year' => 11],
            ['usn' => 'STEM12-001', 'firstname' => 'Bob', 'lastname' => 'Johnson', 'strand' => 'STEM', 'year' => 12],
            ['usn' => 'ABM11-001', 'firstname' => 'Alice', 'lastname' => 'Williams', 'strand' => 'ABM', 'year' => 11],
            ['usn' => 'ABM12-001', 'firstname' => 'Charlie', 'lastname' => 'Brown', 'strand' => 'ABM', 'year' => 12],
        ];

        foreach ($students as $student) {
            User::create([
                'usn' => $student['usn'],
                'firstname' => $student['firstname'],
                'lastname' => $student['lastname'],
                'strand' => $student['strand'],
                'year' => $student['year'],
                'email' => strtolower($student['usn']) . '@student.aclc.edu',
                'password' => Hash::make('password'),
                'user_type' => 'student',
                'has_voted' => false,
            ]);
        }

        // Create election
        $election = Election::create([
            'title' => '2026 Student Council Election',
            'description' => 'Annual student council election',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ]);

        // Create positions
        $president = Position::create([
            'election_id' => $election->id,
            'name' => 'President',
            'description' => 'Student Council President',
            'max_votes' => 1,
            'display_order' => 1,
        ]);

        $representative = Position::create([
            'election_id' => $election->id,
            'name' => 'Representative',
            'description' => 'Course Representative',
            'max_votes' => 1,
            'display_order' => 2,
        ]);

        // Create parties
        $partyA = Party::create([
            'name' => 'Party A',
            'acronym' => 'PA',
            'color' => '#FF5733',
            'description' => 'Party A Description',
        ]);

        $partyB = Party::create([
            'name' => 'Party B',
            'acronym' => 'PB',
            'color' => '#3357FF',
            'description' => 'Party B Description',
        ]);

        // Create president candidates (no course/year filtering)
        Candidate::create([
            'first_name' => 'Presidential',
            'last_name' => 'Candidate One',
            'position_id' => $president->id,
            'party_id' => $partyA->id,
            'course' => null,
            'year_level' => null,
            'bio' => 'A great leader for all students',
        ]);

        Candidate::create([
            'first_name' => 'Presidential',
            'last_name' => 'Candidate Two',
            'position_id' => $president->id,
            'party_id' => $partyB->id,
            'course' => null,
            'year_level' => null,
            'bio' => 'Another great leader',
        ]);

        // Create representative candidates for different courses and years
        // STEM 11 Representative
        Candidate::create([
            'first_name' => 'STEM11',
            'last_name' => 'Representative',
            'position_id' => $representative->id,
            'party_id' => $partyA->id,
            'course' => 'STEM',
            'year_level' => 11,
            'bio' => 'Representative for STEM Grade 11 students',
        ]);

        // STEM 12 Representative
        Candidate::create([
            'first_name' => 'STEM12',
            'last_name' => 'Representative',
            'position_id' => $representative->id,
            'party_id' => $partyB->id,
            'course' => 'STEM',
            'year_level' => 12,
            'bio' => 'Representative for STEM Grade 12 students',
        ]);

        // ABM 11 Representative
        Candidate::create([
            'first_name' => 'ABM11',
            'last_name' => 'Representative',
            'position_id' => $representative->id,
            'party_id' => $partyA->id,
            'course' => 'ABM',
            'year_level' => 11,
            'bio' => 'Representative for ABM Grade 11 students',
        ]);

        // ABM 12 Representative
        Candidate::create([
            'first_name' => 'ABM12',
            'last_name' => 'Representative',
            'position_id' => $representative->id,
            'party_id' => $partyB->id,
            'course' => 'ABM',
            'year_level' => 12,
            'bio' => 'Representative for ABM Grade 12 students',
        ]);
    }
}
