<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\User; 
use Laravel\Sanctum\Sanctum; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\WithFaker; 

class AppointmentApiTest extends TestCase 
{
    use RefreshDatabase, WithFaker; 

    protected $user; 

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'username' => 'testuser_apt_api_feature_new', 
            'password' => bcrypt('password123')
        ]);
    }

    #[Test]
    public function it_can_fetch_appointments_from_api()
    {
        Sanctum::actingAs($this->user); 
        
        $cpf1 = $this->faker->numerify('###########'); // Gere um primeiro CPF
        $cpf2 = $this->faker->numerify('###########'); // Gere um segundo CPF
        $cpf3 = $this->faker->numerify('###########'); // Gere um terceiro CPF

        $cpfs = collect()->times(3, function () {
            return $this->faker->unique()->numerify('###########');
        });

        Appointment::factory()->create([
            'name' => 'Marcos', 
            'cpf' => $cpfs[0], 
            'arrival_date' => now()->today()->format('Y-m-d'),
            'gender' => 'Masculino', 
            'foreign_country' => false, 
        ]); 
        
        Appointment::factory()->create([
            'cpf' => $cpfs[1],
            'arrival_date' => now()->today()->format('Y-m-d'),
            'gender' => 'Masculino', 
            'foreign_country' => false,
        ]); 

        Appointment::factory()->create([
            'cpf' => $cpfs[2], 
            'arrival_date' => now()->today()->format('Y-m-d'),
            'gender' => 'Masculino', 
            'foreign_country' => false,
        ]); 

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Marcos']);
        $response->assertJsonCount(3);
    }
}