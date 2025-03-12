<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class AppointmentControllerTest extends TestCase
{

    use RefreshDatabase;

    #[Test]
    public function it_can_fetch_appointments_from_api()
    {
        $appointment = Appointment::factory()->create();

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
                 ->assertJsonFragment(['cpf' => $appointment->cpf]);
    }

    #[Test]
    public function it_can_create_an_appointment()
    {
        Storage::fake('public');

        $data = [
            'cpf' => '12345678901',
            'name' => 'João',
            'last_name' => 'Silva',
            'date' => '2025-02-16',
            'arrival_date' => '2025-02-16',
            'time' => '18:00',
            'birth_date' => '2000-01-01',
            'state' => 'São Paulo',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Silva',
            'phone' => '(11) 99999-9999',
            'observation' => 'Nenhuma',
            'gender' => 'male',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'accommodation_mode' => 'pernoite',
        ];

        $response = $this->postJson('/api/appointments', $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', ['cpf' => '12345678901']);
    }

    #[Test]
    public function it_does_not_allow_duplicate_cpf()
    {
        Appointment::factory()->create(['cpf' => '12345678901']);

        $data = [
            'cpf' => '12345678901',
            'name' => 'Carlos',
            'last_name' => 'Santos',
            'date' => '2025-02-16',
            'arrival_date' => '2025-02-16',
            'time' => '18:00',
            'birth_date' => '1990-05-10',
            'state' => 'São Paulo',
            'city' => 'São Paulo',
            'mother_name' => 'Ana Santos',
            'phone' => '(11) 99999-9999',
            'observation' => 'Nenhuma',
            'gender' => 'male',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'accommodation_mode' => 'pernoite',
        ];

        $response = $this->postJson('/api/appointments', $data);
        
        $response->assertStatus(409);
    }
}
