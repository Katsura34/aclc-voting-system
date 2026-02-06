<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin users can access admin routes.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = Admin::create([
            'username' => 'admin001',
            'name' => 'Admin User',
            'password' => 'password',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that student users cannot access admin routes.
     */
    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = Student::create([
            'usn' => '2024-001-BS',
            'name' => 'Student One',
            'email' => 'student1@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($student, 'student')->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that student users cannot access admin candidates page.
     */
    public function test_student_cannot_access_admin_candidates(): void
    {
        $student = Student::create([
            'usn' => '2024-002-BS',
            'name' => 'Student Two',
            'email' => 'student2@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($student, 'student')->get('/admin/candidates');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that unauthenticated users cannot access admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that admin users can access admin candidates page.
     */
    public function test_admin_can_access_admin_candidates(): void
    {
        $admin = Admin::create([
            'username' => 'admin002',
            'name' => 'Admin Two',
            'password' => 'password',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/candidates');

        $response->assertStatus(200);
    }

    /**
     * Test that student users cannot access admin elections page.
     */
    public function test_student_cannot_access_admin_elections(): void
    {
        $student = Student::create([
            'usn' => '2024-003-BS',
            'name' => 'Student Three',
            'email' => 'student3@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($student, 'student')->get('/admin/elections');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that student users cannot access admin results page.
     */
    public function test_student_cannot_access_admin_results(): void
    {
        $student = Student::create([
            'usn' => '2024-004-BS',
            'name' => 'Student Four',
            'email' => 'student4@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($student, 'student')->get('/admin/results');

        $response->assertRedirect(route('login'));
    }
}
