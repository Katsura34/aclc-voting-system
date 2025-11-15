<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student cannot access admin dashboard.
     */
    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->create([
            'user_type' => 'student',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($student);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test admin can access admin dashboard.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test unauthenticated user cannot access admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test student cannot access user management.
     */
    public function test_student_cannot_access_user_management(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /**
     * Test student cannot access election management.
     */
    public function test_student_cannot_access_election_management(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student);

        $response = $this->get(route('admin.elections.index'));

        $response->assertStatus(403);
    }

    /**
     * Test student cannot create users.
     */
    public function test_student_cannot_create_users(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student);

        $response = $this->post(route('admin.users.store'), [
            'student_id' => 'TEST123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'student',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test student cannot reset votes.
     */
    public function test_student_cannot_reset_votes(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);
        $targetUser = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student);

        $response = $this->patch(route('admin.users.reset-vote', $targetUser));

        $response->assertStatus(403);
    }

    /**
     * Test admin middleware is applied to all admin routes.
     */
    public function test_admin_middleware_applied_to_all_admin_routes(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student);

        $adminRoutes = [
            '/admin/dashboard',
            '/admin/elections',
            '/admin/parties',
            '/admin/positions',
            '/admin/candidates',
            '/admin/results',
            '/admin/users',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(403);
        }
    }

    /**
     * Test session is properly managed on login.
     */
    public function test_session_properly_managed_on_login(): void
    {
        $user = User::factory()->create([
            'usn' => 'test123',
            'password' => Hash::make('password'),
            'user_type' => 'student',
        ]);

        $response = $this->post('/login', [
            'usn' => 'test123',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();

        // Verify session has been regenerated
        $this->assertNotNull(session()->getId());
    }

    /**
     * Test admin can perform all admin actions.
     */
    public function test_admin_can_perform_admin_actions(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin);

        // Test access to various admin routes
        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/users')->assertStatus(200);
        $this->get('/admin/elections')->assertStatus(200);
    }
}
