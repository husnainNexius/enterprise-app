<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_user_details()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'john@example.com',
            ]);
    }
}
