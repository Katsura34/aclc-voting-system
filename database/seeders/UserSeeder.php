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
            ['firstname' => 'Juan', 'lastname' => 'Dela Cruz', 'usn' => '2024-001-BS', 'strand' => 'STEM', 'year' => 1, 'gender' => 'Male'],
            ['firstname' => 'Maria', 'lastname' => 'Santos', 'usn' => '2024-002-BS', 'strand' => 'ABM', 'year' => 1, 'gender' => 'Female']
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
