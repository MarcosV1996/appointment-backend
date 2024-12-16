<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token()
{
    $user = User::factory()->create([
        'username' => 'testuser',
        'password' => bcrypt('testpassword'),
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => 'testpassword',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message', 'token', 'user'
             ]);

    $token = $response->json('token');

    // Teste com o token
    $response = $this->getJson('/api/user', [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(200);
}


    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }
}
