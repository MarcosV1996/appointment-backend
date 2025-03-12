<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Appointment;

class AppointmentTest extends TestCase
{
    use RefreshDatabase; // Limpa o banco antes de cada teste

    /** @test */
    public function it_can_fetch_appointments_from_api()
    {
        // Criar um agendamento no banco antes do teste
        Appointment::factory()->create([
            'name' => 'Marcos',
            'last_name' => 'Vinicius',
            'cpf' => '12345678900',
        ]);

        // Fazer a requisição GET na API
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Marcos']);
    }
}
