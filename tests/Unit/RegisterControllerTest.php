<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Usuário registrado com sucesso!']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'existing@example.com',
            'password' => 'password',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'role' => '',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email', 'password', 'role']);
    }
}