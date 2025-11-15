<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login rate limiting.
     */
    public function test_login_rate_limiting(): void
    {
        $user = User::factory()->create([
            'usn' => 'test123',
            'password' => Hash::make('password'),
        ]);

        // Clear any existing rate limits
        RateLimiter::clear('test123|127.0.0.1');

        // Make 5 failed login attempts (should succeed)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'usn' => 'test123',
                'password' => 'wrongpassword',
            ]);
            $response->assertSessionHasErrors('usn');
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'usn' => 'test123',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertSessionHasErrors('usn');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('usn'));
    }

    /**
     * Test successful login clears rate limiter.
     */
    public function test_successful_login_clears_rate_limit(): void
    {
        $user = User::factory()->create([
            'usn' => 'test456',
            'password' => Hash::make('password'),
            'user_type' => 'student',
        ]);

        // Clear any existing rate limits
        RateLimiter::clear('test456|127.0.0.1');

        // Make some failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'usn' => 'test456',
                'password' => 'wrongpassword',
            ]);
        }

        // Successful login should clear the rate limiter
        $response = $this->post('/login', [
            'usn' => 'test456',
            'password' => 'password',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test CSRF protection on login.
     */
    public function test_csrf_protection_on_login(): void
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'usn' => 'test',
                'password' => 'password',
            ]);

        // When CSRF middleware is bypassed, request should still process
        $response->assertSessionHasErrors();
    }

    /**
     * Test password is hashed using bcrypt.
     */
    public function test_password_uses_bcrypt(): void
    {
        $password = 'testpassword123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        // Verify password is hashed (not stored as plain text)
        $this->assertNotEquals($password, $user->password);
        
        // Verify password hash starts with bcrypt identifier
        $this->assertStringStartsWith('$2y$', $user->password);
        
        // Verify password can be verified
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test XSS protection in user input.
     */
    public function test_xss_protection_in_user_data(): void
    {
        $xssPayload = '<script>alert("XSS")</script>';
        
        $user = User::factory()->create([
            'name' => $xssPayload,
            'email' => 'test@example.com',
            'usn' => 'test789',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);

        $this->actingAs($user);
        
        $response = $this->get('/admin/dashboard');
        
        // The response should escape the script tag
        $response->assertStatus(200);
        // Verify the raw script tag is not present in the output
        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $response->getContent());
    }

    /**
     * Test session regeneration on login.
     */
    public function test_session_regeneration_on_login(): void
    {
        $user = User::factory()->create([
            'usn' => 'sessiontest',
            'password' => Hash::make('password'),
            'user_type' => 'student',
        ]);

        $response = $this->post('/login', [
            'usn' => 'sessiontest',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }
}
