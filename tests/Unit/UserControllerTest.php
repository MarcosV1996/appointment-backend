<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_upload_a_user_photo()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        
        $photo = UploadedFile::fake()->image('photo.jpg');
        
        $response = $this->postJson("/api/users/{$user->id}/photo", [
            'photo' => $photo,
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['photo']);
        
        Storage::disk('public')->assertExists("photos/{$photo->hashName()}");
    }

    #[Test]
    public function it_returns_error_if_photo_upload_fails()
    {
        $user = User::factory()->create();
        
        $response = $this->postJson("/api/users/{$user->id}/photo", []);
        
        $response->assertStatus(400)
                 ->assertJson(['message' => 'Erro ao fazer upload da foto.']);
    }
    

    #[Test]
    public function it_can_show_a_user()
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'role' => 'admin',
            'photo' => 'photos/example.jpg'
        ]);
        
        $response = $this->getJson("/api/users/{$user->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'username' => 'johndoe',
                     'role' => 'admin',
                     'photo' => 'photos/example.jpg'
                 ]);
    }

    #[Test]
    public function it_returns_404_if_user_not_found()
    {
        $response = $this->getJson("/api/users/999");
        
        $response->assertStatus(404)
                 ->assertJson(['message' => 'Usuário não encontrado.']);
    }
}
