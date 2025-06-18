<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\WithFaker;

class AppointmentFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria um usuário administrador antes de cada teste.
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    #[Test]
    public function can_fetch_appointments()
    {
        Sanctum::actingAs($this->adminUser);
        
        // CORREÇÃO: Usamos um loop para garantir que cada CPF seja único.
        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'cpf' => $this->faker->unique()->numerify('###########'),
                'phone' => '11999999999', // Telefone válido
            ]);
        }
        
        $response = $this->getJson('/api/appointments');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    #[Test]
    public function can_create_appointment()
    {
        Sanctum::actingAs($this->adminUser);
        
        // CORREÇÃO: Garante que o telefone enviado seja válido.
        $appointmentData = [
            'name' => 'João Feature',
            'last_name' => 'Silva Feature',
            'cpf' => '52998224725',
            'date' => now()->addDay()->format('Y-m-d'),
            'arrival_date' => now()->format('Y-m-d'),
            'time' => '10:00',
            'birth_date' => '1990-01-01',
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Silva Feature',
            'phone' => '11988887777', // Telefone com 11 dígitos
            'observation' => 'Nenhuma',
            'gender' => 'male', 
            'foreign_country' => false,
            'accommodation_mode' => 'pernoite',
        ];
        
        $response = $this->postJson('/api/appointments', $appointmentData);
        
        $response->assertStatus(201);
    }

    #[Test]
    public function cannot_create_duplicate_cpf()
    {
        Sanctum::actingAs($this->adminUser);
        
        // CORREÇÃO: Garante que a primeira criação use dados válidos.
        Appointment::factory()->create([
            'cpf' => '52998224725',
            'phone' => '11988887777', // Telefone válido
        ]);
        
        $duplicateData = [
            'name' => 'Maria Duplicate',
            'last_name' => 'Santos Duplicate',
            'cpf' => '52998224725', // CPF duplicado intencionalmente
            'date' => now()->addDays(2)->format('Y-m-d'),
            'arrival_date' => now()->format('Y-m-d'),
            'time' => '11:00',
            'birth_date' => '1992-02-02',
            'state' => 'RJ',
            'city' => 'Rio de Janeiro',
            'mother_name' => 'Ana Santos Duplicate',
            'phone' => '21888888888', // Telefone válido
            'observation' => 'Nenhuma',
            'gender' => 'female', 
            'foreign_country' => false,
            'accommodation_mode' => 'pernoite',
        ];
        
        $response = $this->postJson('/api/appointments', $duplicateData);
        
        $response->assertStatus(409); 
    }

    #[Test]
    public function can_update_appointment()
    {
        Sanctum::actingAs($this->adminUser);
        
        // CORREÇÃO: Cria um agendamento com dados válidos.
        $appointment = Appointment::factory()->create([
            'cpf' => $this->faker->unique()->numerify('###########'),
            'phone' => '41911112222',
        ]);

        $updatedData = [
            'name' => 'Carlos Atualizado',
            'last_name' => $appointment->last_name,
            'cpf' => $appointment->cpf, // Mantém o CPF
            'date' => $appointment->date,
            'arrival_date' => $appointment->arrival_date,
            'time' => '14:30',
            'birth_date' => $appointment->birth_date,
            'state' => $appointment->state,
            'city' => $appointment->city,
            'mother_name' => $appointment->mother_name,
            'phone' => '42988776655', // Telefone válido
            'observation' => 'Consulta alterada',
            'gender' => 'male', 
            'foreign_country' => $appointment->foreign_country,
            'accommodation_mode' => 'pernoite',
        ];

        $response = $this->putJson("/api/appointments/{$appointment->id}", $updatedData);

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_login()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    /**
     * Testa se um usuário autenticado pode acessar a lista de agendamentos.
     * Este é o teste que você solicitou.
     */
    #[Test]
    public function authenticated_user_can_access_appointments(): void
    {
        // 1. PREPARAÇÃO: Autentica o usuário administrador.
        Sanctum::actingAs($this->adminUser);
        
        // Cria alguns agendamentos com dados únicos para o teste.
        Appointment::factory()->create(['cpf' => $this->faker->unique()->numerify('###########'), 'phone' => '11111111111']);
        Appointment::factory()->create(['cpf' => $this->faker->unique()->numerify('###########'), 'phone' => '22222222222']);

        // 2. AÇÃO: Simula uma requisição GET para a rota protegida.
        $response = $this->getJson('/api/appointments');

        // 3. VERIFICAÇÃO: Garante que o acesso foi permitido.
        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }
}
