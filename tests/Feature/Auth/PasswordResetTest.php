<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();
    
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'testuser@example.com',
        ]);
    
        $this->post('/api/forgot-password', ['email' => $user->email])
        ->assertStatus(200);
   
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/api/reset-password', [
                'token' => $notification->token,
                'email' => $user->email, 
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);
    
            $response->assertStatus(200);
            return true;
        });
    }
}
