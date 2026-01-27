<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        // Create a user
        $user = \App\Models\User::factory()->create([
            'email' => 'john@example.com',
            'password' => \Hash::make('password123'),
        ]);

        // Attempt to login with valid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Assert that the login was successful
        $response->assertStatus(200);
    }
}
