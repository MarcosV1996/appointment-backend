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
<<<<<<< HEAD
>>>>>>> Atualização de Testes
=======
>>>>>>> Initial commit - Laravel backend
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Initial commit - Laravel backend
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }
<<<<<<< HEAD
=======
=======
>>>>>>> Atualização de Testes
=======
>>>>>>> Initial commit - Laravel backend
    #[Test]
    public function users_can_logout()
    {
        // Criar usuário e gerar um token de autenticação
        $user = User::factory()->create();
        $token = $user->createToken('MeuToken')->plainTextToken;
    
        // Enviar requisição de logout autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
    
        // Garantir que o token foi excluído corretamente
        $this->assertCount(0, $user->tokens);
    
        $response->assertNoContent();
    }
    
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> feature/TestsFeaturesAndUnit
=======
>>>>>>> Atualização de Testes
=======
>>>>>>> Initial commit - Laravel backend
}
