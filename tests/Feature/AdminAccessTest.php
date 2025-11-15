<?php

namespace Tests\Feature;

use App\Models\User;
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
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN001',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that student users cannot access admin routes.
     */
    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-001-BS',
        ]);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertRedirect(route('voting.index'));
        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }

    /**
     * Test that student users cannot access admin candidates page.
     */
    public function test_student_cannot_access_admin_candidates(): void
    {
        $student = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-002-BS',
        ]);

        $response = $this->actingAs($student)->get('/admin/candidates');

        $response->assertRedirect(route('voting.index'));
        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }

    /**
     * Test that unauthenticated users cannot access admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test that admin users can access admin candidates page.
     */
    public function test_admin_can_access_admin_candidates(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'usn' => 'ADMIN002',
        ]);

        $response = $this->actingAs($admin)->get('/admin/candidates');

        $response->assertStatus(200);
    }

    /**
     * Test that student users cannot access admin elections page.
     */
    public function test_student_cannot_access_admin_elections(): void
    {
        $student = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-003-BS',
        ]);

        $response = $this->actingAs($student)->get('/admin/elections');

        $response->assertRedirect(route('voting.index'));
        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }

    /**
     * Test that student users cannot access admin results page.
     */
    public function test_student_cannot_access_admin_results(): void
    {
        $student = User::factory()->create([
            'user_type' => 'student',
            'usn' => '2024-004-BS',
        ]);

        $response = $this->actingAs($student)->get('/admin/results');

        $response->assertRedirect(route('voting.index'));
        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }
}
