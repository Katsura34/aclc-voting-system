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
            'name' => 'Admin User',
            'email' => 'admin@aclc.edu.ph',
            'usn' => 'ADMIN001',
            'user_type' => 'admin',
            'has_voted' => false,
            'password' => Hash::make('password'),
        ]);

        // Create Student Users
        $students = [
            ['name' => 'Juan Dela Cruz', 'usn' => '2024-001-BS'],
            ['name' => 'Maria Santos', 'usn' => '2024-002-BS'],
            ['name' => 'Pedro Garcia', 'usn' => '2024-003-BS'],
            ['name' => 'Ana Reyes', 'usn' => '2024-004-BS'],
            ['name' => 'Jose Fernandez', 'usn' => '2024-005-BS'],
            ['name' => 'Carmen Lopez', 'usn' => '2024-006-BS'],
            ['name' => 'Miguel Torres', 'usn' => '2024-007-BS'],
            ['name' => 'Sofia Ramirez', 'usn' => '2024-008-BS'],
            ['name' => 'Luis Mendoza', 'usn' => '2024-009-BS'],
            ['name' => 'Isabella Cruz', 'usn' => '2024-010-BS'],
            ['name' => 'Carlos Morales', 'usn' => '2024-011-BS'],
            ['name' => 'Elena Gonzales', 'usn' => '2024-012-BS'],
            ['name' => 'Rafael Castillo', 'usn' => '2024-013-BS'],
            ['name' => 'Lucia Martinez', 'usn' => '2024-014-BS'],
            ['name' => 'Diego Navarro', 'usn' => '2024-015-BS'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => strtolower(str_replace(' ', '.', $student['name'])) . '@student.aclc.edu.ph',
                'usn' => $student['usn'],
                'user_type' => 'student',
                'has_voted' => false,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
