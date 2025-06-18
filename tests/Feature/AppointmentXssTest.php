<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentXssTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_xss_test_feature',
            'password' => bcrypt('password123')
        ]);
    }

    #[Test]
    public function xss_protection_on_appointment_creation()
    {
        Sanctum::actingAs($this->adminUser);

        $xssAttempt = '<script>alert("XSS")</script>';
        $possibleSanitized = [
            '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
            'alert(&quot;XSS&quot;)',
            htmlspecialchars($xssAttempt)
        ];

        $appointmentData = [
            'name' => 'Test XSS Feature',
            'last_name' => 'User XSS Feature',
            'cpf' => '52998224725',
            'date' => now()->addDay()->format('Y-m-d'),
            'arrival_date' => now()->format('Y-m-d'),
            'time' => '10:00',
            'birth_date' => '1990-01-01',
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => 'Maria Doe XSS',
            'phone' => '11999999999', 
            'observation' => $xssAttempt,
            'gender' => 'male',
            'foreign_country' => false,
            'no_phone' => false, 
            'is_hidden' => false, 
            'replace' => false,   
            'show_more' => false, 
            'accommodation_mode' => 'pernoite',
        ];

        $response = $this->postJson('/api/appointments', $appointmentData);
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Agendamento realizado com sucesso!'
                 ]);

        $responseObservation = $response->json('appointment.observation');
        
        $this->assertContains(
            $responseObservation,
            $possibleSanitized,
            "O campo 'Observação' foi salvo de forma insegura no sistema: {$responseObservation}"
        );

        // Verificação no banco de dados
        $dbObservation = Appointment::first()->observation;
        $this->assertContains(
            $dbObservation,
            $possibleSanitized,
            "A observação no banco não foi sanitizada corretamente. Recebido: {$dbObservation}"
        );
    }
}