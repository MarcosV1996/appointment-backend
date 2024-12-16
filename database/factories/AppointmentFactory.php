<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Provider\pt_BR\Person;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        $this->faker->addProvider(new Person($this->faker));
    
        return [
            'name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
             'cpf' => '11144477735', // CPF VÁLIDO sem pontos e traço
            'date' => $this->faker->date(),
            'arrival_date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'birth_date' => $this->faker->date(),
            'state' => 'SP',
            'city' => 'São Paulo',
            'mother_name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'observation' => $this->faker->sentence(),
            'gender' => 'Masculino',
            'foreign_country' => false,
            'noPhone' => false,
            'isHidden' => false,
            'replace' => false,
            'showMore' => false,
            'photo' => 'path/to/photo',
        ];
    }
}
