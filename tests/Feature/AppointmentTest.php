<?php

namespace Tests\Unit;

use Tests\TestCase;

class AppointmentTest extends TestCase
{
    public function test_example()
    {
        $response = $this->get('/api/appointments');
        $response->assertStatus(200);
    }
}
