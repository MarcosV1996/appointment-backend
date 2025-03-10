<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token()
{
    // Crie um usuário com as credenciais de teste
    $user = \App\Models\User::factory()->create([
        'username' => 'testuser',
        'password' => bcrypt('testpassword'), // A senha precisa ser criptografada
    ]);

    // As credenciais que serão passadas para o login
    $credentials = [
        'username' => 'testuser',
        'password' => 'testpassword',
    ];

    $response = $this->postJson('/api/login', $credentials);
    $response->assertStatus(200);

    $response->assertJsonStructure([
        'message',
        'token',
        'user'
    ]);
}

}