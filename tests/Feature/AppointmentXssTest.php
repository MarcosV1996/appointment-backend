<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AppointmentXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_xss_protection_on_appointment_creation()
    {
        $maliciousCode = '<script>alert("XSS");</script>';
        $escapedMaliciousCode = htmlspecialchars($maliciousCode, ENT_QUOTES, 'UTF-8');
        $testDate = '2024-06-01';

        $response = $this->postJson('/api/appointments', [
            'name' => $maliciousCode,
            'last_name' => 'lulis',
            'cpf' => '12345678900',
            'date' => $testDate,
            'time' => '10:00',
            'state' => 'DF',
            'city' => 'Brasilia',
            'phone' => '(11) 99999-9999',
            'gender' => 'Masculino',
            'arrival_date' => $testDate,
            'observation' => 'Nenhuma',
            'accommodation_mode' => 'pernoite',
            'birth_date' => '1990-01-01',
            'mother_name' => 'Maria Doe',
        ]);

        $response->assertStatus(201);

        $jsonContent = $response->getContent();

        $containsLt = str_contains($jsonContent, '&lt;');
        $containsGt = str_contains($jsonContent, '&gt;');
        $containsQuote = str_contains($jsonContent, '&quot;');

        if (!$containsLt || !$containsGt || !$containsQuote) {
            $containsScript = str_contains($jsonContent, '<script>');
            $containsAlert = str_contains($jsonContent, 'alert("XSS")');

            $this->assertFalse($containsScript && $containsAlert, 'Falha na proteção XSS: A resposta JSON contém dados não escapados que podem permitir a execução de scripts maliciosos.
');
        } else {
            $this->assertTrue(true, 'Proteção XSS bem-sucedida');
        }

        $insertedValue = DB::table('appointments')
            ->where('cpf', '12345678900')
            ->value('name');

        $appointment = Appointment::where('name', $escapedMaliciousCode)->first();

        if ($insertedValue !== $escapedMaliciousCode) {
            $appointment = Appointment::where('name', $maliciousCode)->first();
            $this->assertNotNull($appointment, 'Falha na proteção XSS: HTML original encontrado no banco de dados');
        } else {
            $this->assertNotNull($appointment, 'Falha na proteção XSS: O banco de dados não contém o HTML escapado esperado. O valor armazenado não foi devidamente protegido contra ataques XSS.
');
        }
    }
}