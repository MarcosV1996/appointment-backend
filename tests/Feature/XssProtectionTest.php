<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class XssProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_xss_protection_blocks_script_tags()
    {
        $response = $this->post('/api/register', [
            'name' => 'Test User', 
            'username' => '<script>alert("XSS")</script>',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseMissing('users', [
            'username' => '<script>alert("XSS")</script>'
        ]);
    }
}
