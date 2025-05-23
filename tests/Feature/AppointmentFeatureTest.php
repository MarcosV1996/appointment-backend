<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

class AppointmentFeatureTest extends TestCase
{
    use RefreshDatabase; 

    public function test_can_fetch_appointments()
    {
        // Criando um agendamento antes do teste
        Appointment::factory()->create([
            'cpf' => '12345678900',
            'name' => 'Marcos  Vinicius',
            'last_name' => 'Doe',
            'date' => now()->format('Y-m-d'),
            'arrival_date' => now()->format('Y-m-d'),
            'time' => '10:00',
            'birth_date' => '1990-01-01',
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Doe',
            'phone' => '(11) 99999-9999',
            'observation' => 'Nenhuma',
            'gender' => 'Masculino',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'photo' => null,
            'accommodation_mode' => 'pernoite',
        ]);

        // Chamando a API
        $response = $this->get('/api/appointments');

        dump($response->json()); // Exibe a resposta da API para depuração

        // Verifica se o status da resposta está correto
        $response->assertStatus(200);
    }

    public function test_can_create_appointment()
    {
        $appointmentData = [
            'name' => 'Lulis',
            'last_name' => 'Da silva',
            'cpf' => '12345678900',
            'date' => '2025-02-02',
            'arrival_date' => '2025-02-01',
            'time' => '12:00',
            'birth_date' => '1990-01-01',
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Doe',
            'phone' => '(11) 99999-9999', 
            'observation' => 'Nenhuma',
            'gender' => 'Masculino',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'photo' => UploadedFile::fake()->image('profile.jpg'), 
            'accommodation_mode' => 'pernoite', 
        ];
    
        dump($appointmentData); 
    
        $response = $this->post('/api/appointments', $appointmentData);
        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', ['cpf' => '12345678900']);
    }
    

    public function test_cannot_create_duplicate_cpf()
    {
        // Primeiro, cria um agendamento válido
        $appointmentData = [
            'name' => 'Lulis',
            'last_name' => 'Da silva',
            'cpf' => '12345678900', 
            'date' => '2025-02-02',
            'arrival_date' => '2025-02-01',
            'time' => '12:00',
            'birth_date' => '1990-01-01',
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Doe',
            'phone' => '(11) 99999-9999', 
            'observation' => 'Nenhuma',
            'gender' => 'Masculino',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'photo' => UploadedFile::fake()->image('profile.jpg'), 
            'accommodation_mode' => 'pernoite',
        ];
    
        $this->post('/api/appointments', $appointmentData);
    
        // Tenta criar o mesmo CPF novamente
        $response = $this->post('/api/appointments', $appointmentData);
    
        $response->assertStatus(409);
    }
    
    public function test_can_update_appointment()
    {
        $appointment = Appointment::factory()->create();
        $updatedData = ['name' => 'Nome Atualizado'];
        $response = $this->put("/api/appointments/{$appointment->id}", $updatedData);
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'name' => 'Nome Atualizado']);
    }

    public function test_can_delete_appointment()
    {
        $appointment = Appointment::factory()->create();
        $response = $this->delete("/api/appointments/{$appointment->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/api/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_authenticated_user_can_access_appointments()
{
    $user = User::factory()->create(); // Cria um usuário para autenticação
    $token = $user->createToken('TestToken')->plainTextToken; // Cria um token válido

    $this->withHeaders([
        'Authorization' => "Bearer $token"
    ])->get('/api/appointments')
      ->assertStatus(200); // Confirma que o usuário autenticado pode acessar
}

}
