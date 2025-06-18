<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        // 1. PREPARAÇÃO: Define dados válidos para o novo utilizador.
        $userData = [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ];

        // 2. AÇÃO: Simula uma requisição POST para a API de registo.
        $response = $this->postJson('/api/register', $userData);

        // 3. VERIFICAÇÃO: Garante que a resposta da API está correta.
        $response
            ->assertStatus(201) // Verifica se o status HTTP é 201 (Created).
            ->assertJsonStructure(['message', 'user', 'token']); // Verifica a estrutura básica do JSON.

        // VERIFICAÇÃO EXTRA: Garante que o utilizador foi salvo corretamente na base de dados.
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'username' => $userData['username'],
        ]);
    }
    
    #[Test]
    public function user_cannot_register_with_existing_email(): void
    {
        // PREPARAÇÃO: Cria um utilizador com um email que já existe.
        User::factory()->create(['email' => 'existing@example.com']);

        $newUserData = [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => 'existing@example.com', // Tenta usar o mesmo email.
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ];

        // AÇÃO: Tenta registar com o email duplicado.
        $response = $this->postJson('/api/register', $newUserData);

        // VERIFICAÇÃO: Garante que a API rejeitou com um erro de validação para o campo 'email'.
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_register_with_invalid_data(): void
    {
        // PREPARAÇÃO: Cria um array com vários tipos de dados inválidos.
        $invalidData = [
            'name' => '',                   // Nome em branco.
            'username' => '',               // Username em branco.
            'email' => 'email-invalido',    // Formato de email incorreto.
            'password' => '123',            // Senha muito curta.
            'password_confirmation' => 'diferente', // Senha e confirmação não coincidem.
            'role' => 'papel_inexistente',  // 'role' que não é permitida.
        ];

        // AÇÃO: Tenta registar com os dados inválidos.
        $response = $this->postJson('/api/register', $invalidData);

        // VERIFICAÇÃO: Garante que a API rejeitou e retornou erros para todos os campos inválidos.
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email', 'password', 'role']);
    }

    #[Test]
    public function a_user_can_register_and_xss_is_prevented()
    {
        // PREPARAÇÃO: Define um username com um script malicioso.
        $xssUsername = '<script>alert("XSS")</script>';
        $userData = [
            'name' => 'Test User XSS',
            'username' => $xssUsername,
            'email' => 'xss_test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', 
        ];

        // AÇÃO: Tenta registar com o script.
        $response = $this->postJson('/api/register', $userData);

        // VERIFICAÇÃO: Garante que o utilizador foi criado, mas com o script removido (sanitizado).
        $response
            ->assertStatus(201)
            ->assertJsonPath('user.username', 'alert("XSS")');

        $this->assertDatabaseHas('users', ['username' => 'alert("XSS")']);
        $this->assertDatabaseMissing('users', ['username' => $xssUsername]);
    }
    
    #[Test]
    public function a_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt($password = 'i-love-TSI')]);

        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => $password,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    #[Test]
    public function a_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    /**
     * Testa se um utilizador não autenticado é bloqueado ao tentar aceder a uma rota protegida.
     * Este é o novo teste que você solicitou.
     */
    #[Test]
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        // 1. PREPARAÇÃO
        // A preparação é não fazer nada, ou seja, não autenticar um utilizador.
        // A requisição será feita como um visitante anónimo.
        
        // 2. AÇÃO
        // Tentamos aceder a uma rota que deve ser protegida, como a que lista todos os utilizadores.
        // Se a sua rota `/api/users` não for protegida, pode usar outra, como `/api/appointments`.
        $response = $this->getJson('/api/users');

        // 3. VERIFICAÇÃO
        // Verificamos se a API bloqueou corretamente o acesso com o status 401 Unauthorized.
        // Este é o comportamento esperado para rotas protegidas pelo Sanctum.
        $response->assertStatus(401);
    }
}
