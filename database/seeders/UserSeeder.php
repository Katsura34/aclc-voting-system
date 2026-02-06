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
            'first_name' => 'Admin',
            'last_name' => 'User',
            'usn' => 'ADMIN001',
            'strand' => 'ADMIN',
            'year' => 'N/A',
            'gender' => 'N/A',
            'user_type' => 'admin',
            'has_voted' => false,
            'password' => Hash::make('password'),
        ]);

        // Create Student Users
        $students = [
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'usn' => '2024-001-BS'],
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'usn' => '2024-002-BS'],
            ['first_name' => 'Pedro', 'last_name' => 'Garcia', 'usn' => '2024-003-BS'],
            ['first_name' => 'Ana', 'last_name' => 'Reyes', 'usn' => '2024-004-BS'],
            ['first_name' => 'Jose', 'last_name' => 'Fernandez', 'usn' => '2024-005-BS'],
            ['first_name' => 'Carmen', 'last_name' => 'Lopez', 'usn' => '2024-006-BS'],
            ['first_name' => 'Miguel', 'last_name' => 'Torres', 'usn' => '2024-007-BS'],
            ['first_name' => 'Sofia', 'last_name' => 'Ramirez', 'usn' => '2024-008-BS'],
            ['first_name' => 'Luis', 'last_name' => 'Mendoza', 'usn' => '2024-009-BS'],
            ['first_name' => 'Isabella', 'last_name' => 'Cruz', 'usn' => '2024-010-BS'],
            ['first_name' => 'Carlos', 'last_name' => 'Morales', 'usn' => '2024-011-BS'],
            ['first_name' => 'Elena', 'last_name' => 'Gonzales', 'usn' => '2024-012-BS'],
            ['first_name' => 'Rafael', 'last_name' => 'Castillo', 'usn' => '2024-013-BS'],
            ['first_name' => 'Lucia', 'last_name' => 'Martinez', 'usn' => '2024-014-BS'],
            ['first_name' => 'Diego', 'last_name' => 'Navarro', 'usn' => '2024-015-BS'],
        ];

        foreach ($students as $student) {
            User::create([
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'usn' => $student['usn'],
                'strand' => 'STEM',
                'year' => '1st Year',
                'gender' => 'Unspecified',
                'user_type' => 'student',
                'has_voted' => false,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
