<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\WithFaker;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'username' => 'junior',
            'password' => bcrypt('password123')
        ]);

        Storage::fake('public'); 
    }

#[Test]
public function it_can_upload_a_user_photo()
{
    Storage::fake('public');

    Sanctum::actingAs($this->adminUser);
    $user = User::factory()->create();

    $photo = UploadedFile::fake()->create(
        'profile.jpg',
        500, // tamanho em KB
        'image/jpeg'
    );

    $response = $this->postJson("/api/users/{$user->id}/photo", [
        'photo' => $photo
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['photo']);

    $user->refresh();
    Storage::disk('public')->assertExists($user->photo);
}

#[Test]
public function it_returns_error_if_photo_upload_fails()
{
    $user = User::factory()->create();
    Sanctum::actingAs($this->adminUser);

    // Teste sem arquivo
    $response = $this->postJson("/api/users/{$user->id}/photo", []);
    $response->assertStatus(422); // Unprocessable Entity
    
    // Teste com arquivo inválido
    $response = $this->postJson("/api/users/{$user->id}/photo", [
        'photo' => 'not-a-file'
    ]);
    $response->assertStatus(422);
}

    #[Test]
    public function it_can_show_a_user()
    {
        Sanctum::actingAs($this->adminUser);
        $user = User::factory()->create([
            'username' => 'marquinhos',
            'email' => 'marquinhos@example.com',
            'role' => 'user', 
        ]);
        
        $response = $this->getJson("/api/users/{$user->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'username' => 'marquinhos', 
                     'role' => 'user', 
                 ])
                 ->assertJsonMissing(['email']);
    }

    #[Test]
    public function it_returns_404_if_user_not_found()
    {
        Sanctum::actingAs($this->adminUser); 
        $response = $this->getJson("/api/users/9999"); 
        
        $response->assertStatus(404);
    }
}