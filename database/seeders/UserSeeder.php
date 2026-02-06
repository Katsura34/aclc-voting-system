<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@aclc.edu.ph',
            'usn' => 'ADMIN001',
            'user_type' => 'admin',
            'has_voted' => false,
            'password' => Hash::make('password'),
        ]);

        // Create Student Users
        $students = [
            ['firstname' => 'Juan', 'lastname' => 'Dela Cruz', 'usn' => '2024-001-BS', 'strand' => 'STEM', 'year' => '1st Year', 'gender' => 'Male'],
            ['firstname' => 'Maria', 'lastname' => 'Santos', 'usn' => '2024-002-BS', 'strand' => 'ABM', 'year' => '1st Year', 'gender' => 'Female'],
            ['firstname' => 'Pedro', 'lastname' => 'Garcia', 'usn' => '2024-003-BS', 'strand' => 'HUMSS', 'year' => '2nd Year', 'gender' => 'Male'],
            ['firstname' => 'Ana', 'lastname' => 'Reyes', 'usn' => '2024-004-BS', 'strand' => 'STEM', 'year' => '2nd Year', 'gender' => 'Female'],
            ['firstname' => 'Jose', 'lastname' => 'Fernandez', 'usn' => '2024-005-BS', 'strand' => 'ABM', 'year' => '3rd Year', 'gender' => 'Male'],
            ['firstname' => 'Carmen', 'lastname' => 'Lopez', 'usn' => '2024-006-BS', 'strand' => 'STEM', 'year' => '3rd Year', 'gender' => 'Female'],
            ['firstname' => 'Miguel', 'lastname' => 'Torres', 'usn' => '2024-007-BS', 'strand' => 'HUMSS', 'year' => '4th Year', 'gender' => 'Male'],
            ['firstname' => 'Sofia', 'lastname' => 'Ramirez', 'usn' => '2024-008-BS', 'strand' => 'ABM', 'year' => '4th Year', 'gender' => 'Female'],
            ['firstname' => 'Luis', 'lastname' => 'Mendoza', 'usn' => '2024-009-BS', 'strand' => 'STEM', 'year' => '1st Year', 'gender' => 'Male'],
            ['firstname' => 'Isabella', 'lastname' => 'Cruz', 'usn' => '2024-010-BS', 'strand' => 'HUMSS', 'year' => '1st Year', 'gender' => 'Female'],
            ['firstname' => 'Carlos', 'lastname' => 'Morales', 'usn' => '2024-011-BS', 'strand' => 'ABM', 'year' => '2nd Year', 'gender' => 'Male'],
            ['firstname' => 'Elena', 'lastname' => 'Gonzales', 'usn' => '2024-012-BS', 'strand' => 'STEM', 'year' => '2nd Year', 'gender' => 'Female'],
            ['firstname' => 'Rafael', 'lastname' => 'Castillo', 'usn' => '2024-013-BS', 'strand' => 'HUMSS', 'year' => '3rd Year', 'gender' => 'Male'],
            ['firstname' => 'Lucia', 'lastname' => 'Martinez', 'usn' => '2024-014-BS', 'strand' => 'ABM', 'year' => '3rd Year', 'gender' => 'Female'],
            ['firstname' => 'Diego', 'lastname' => 'Navarro', 'usn' => '2024-015-BS', 'strand' => 'STEM', 'year' => '4th Year', 'gender' => 'Male'],
        ];

        foreach ($students as $student) {
            User::create([
                'firstname' => $student['firstname'],
                'lastname' => $student['lastname'],
                'email' => strtolower($student['firstname'] . '.' . $student['lastname']) . '@student.aclc.edu.ph',
                'usn' => $student['usn'],
                'strand' => $student['strand'],
                'year' => $student['year'],
                'gender' => $student['gender'],
                'user_type' => 'student',
                'has_voted' => false,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
