<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use App\Models\Party;
use App\Models\Candidate;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'usn' => 'admin',
            'password' => Hash::make('password'),
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@aclc.edu',
            'strand' => 'STEM',
            'year' => 12,
            'user_type' => 'admin',
        ]);

        // Create student users
        User::create([
            'usn' => 'student1',
            'password' => Hash::make('password'),
            'firstname' => 'Student',
            'lastname' => 'One',
            'email' => 'student1@aclc.edu',
            'strand' => 'STEM',
            'year' => 11,
            'user_type' => 'student',
        ]);

        User::create([
            'usn' => 'student2',
            'password' => Hash::make('password'),
            'firstname' => 'Student',
            'lastname' => 'Two',
            'email' => 'student2@aclc.edu',
            'strand' => 'ABM',
            'year' => 12,
            'user_type' => 'student',
        ]);

        // Create election
        $election = Election::create([
            'title' => '2026 Student Council Election',
            'description' => 'Annual student council election for academic year 2026-2027',
            'start_date' => now(),
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

        $vicePresident = Position::create([
            'election_id' => $election->id,
            'name' => 'Vice President',
            'description' => 'Student Council Vice President',
            'max_votes' => 1,
            'display_order' => 2,
        ]);

        $secretary = Position::create([
            'election_id' => $election->id,
            'name' => 'Secretary',
            'description' => 'Student Council Secretary',
            'max_votes' => 1,
            'display_order' => 3,
        ]);

        // Create parties
        $partyA = Party::create([
            'name' => 'Progressive Student Alliance',
            'acronym' => 'PSA',
            'color' => '#0066cc',
            'description' => 'A party focused on progressive student policies',
        ]);

        $partyB = Party::create([
            'name' => 'United Students Movement',
            'acronym' => 'USM',
            'color' => '#cc3300',
            'description' => 'A party dedicated to student unity and representation',
        ]);

        // Create candidates for President
        Candidate::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'position_id' => $president->id,
            'party_id' => $partyA->id,
            'course' => 'STEM',
            'year_level' => 12,
            'bio' => 'Experienced student leader with a passion for change and innovation.',
            'platform' => 'I will work to improve student facilities, enhance communication between students and administration, and create more opportunities for student involvement.',
        ]);

        Candidate::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'position_id' => $president->id,
            'party_id' => $partyB->id,
            'course' => 'ABM',
            'year_level' => 12,
            'bio' => 'Dedicated to serving the student body with integrity and transparency.',
            'platform' => 'My platform focuses on student welfare, academic support, and creating a more inclusive campus environment.',
        ]);

        // Create candidates for Vice President
        Candidate::create([
            'first_name' => 'Jose',
            'last_name' => 'Reyes',
            'position_id' => $vicePresident->id,
            'party_id' => $partyA->id,
            'course' => 'HUMSS',
            'year_level' => 11,
            'bio' => 'A committed student advocate with strong organizational skills.',
            'platform' => 'I will support the president in implementing progressive policies and ensure student voices are heard.',
        ]);

        Candidate::create([
            'first_name' => 'Ana',
            'last_name' => 'Garcia',
            'position_id' => $vicePresident->id,
            'party_id' => $partyB->id,
            'course' => 'STEM',
            'year_level' => 11,
            'bio' => 'Passionate about student rights and academic excellence.',
            'platform' => 'I will work closely with the president to ensure effective leadership and student representation.',
        ]);

        // Create candidates for Secretary
        Candidate::create([
            'first_name' => 'Pedro',
            'last_name' => 'Cruz',
            'position_id' => $secretary->id,
            'party_id' => $partyA->id,
            'course' => 'ABM',
            'year_level' => 11,
            'bio' => 'Detail-oriented and organized student with excellent communication skills.',
            'platform' => 'I will ensure accurate record-keeping and transparent communication of student council activities.',
        ]);

        Candidate::create([
            'first_name' => 'Isabella',
            'last_name' => 'Lopez',
            'position_id' => $secretary->id,
            'party_id' => $partyB->id,
            'course' => 'STEM',
            'year_level' => 12,
            'bio' => 'Experienced in documentation and committed to transparency.',
            'platform' => 'I will maintain clear records and keep students informed about council decisions and activities.',
        ]);

        $this->command->info('Test data seeded successfully!');
    }
}
