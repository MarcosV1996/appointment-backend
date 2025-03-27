<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;


class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_user()
    {
        $data = [
            'name' => 'João Silva',
            'username' => 'joaosilva',
            'password' => 'senha123',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/register', $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['username' => 'joaosilva']);
    }

    #[Test]
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'username' => 'joaosilva',
            'password' => Hash::make('senha123'),
        ]);

        $data = ['username' => 'joaosilva', 'password' => 'senha123'];
        $response = $this->postJson('/api/login', $data);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user']);
    }

    #[Test]
    public function it_fails_login_with_invalid_credentials()
    {
        $data = ['username' => 'invalido', 'password' => 'senhaerrada'];
        $response = $this->postJson('/api/login', $data);
        
        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_logout_a_user()
    {
        // Criar um usuário e gerar um token de autenticação
        $user = User::factory()->create();
        $token = $user->createToken('MeuToken')->plainTextToken;
    
        // Enviar requisição de logout autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
    
        // Garantir que o token foi excluído corretamente
        $this->assertCount(0, $user->tokens);
    
        $response->assertStatus(204);
    }
    
    #[Test]
    public function it_fails_registration_with_invalid_data()
    {
        $data = [
            'name' => '',
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'role' => '',
        ];
    
        $response = $this->postJson('/api/register', $data);
    
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'username', 'email', 'password', 'role']);
    }

    #[Test]
    public function test_user_can_successfully_reset_password()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);

        Notification::assertSentTo(
            [$user],
            ResetPassword::class,
            function ($notification, $channels, $notifiable) use ($user) {

                $this->assertEquals($user->email, $notifiable->email);
                $this->assertNotEmpty($notification->token);

                $token = $notification->token;

                $response = $this->postJson('/api/reset-password', [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ]);

                $response->assertStatus(200);

                $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

                return true;
            }
        );
    }
}
