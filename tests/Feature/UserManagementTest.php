<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker; // RefreshDatabase limpa o DB para cada teste

    protected User $adminUser;
    protected User $employeeUser;
    protected User $regularUser; // Para usuários que não têm role no sistema, se houver

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuários com diferentes roles para simular acessos
        // Usar faker->unique() para username e email para evitar colisões entre os próprios usuários base
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_' . $this->faker->unique()->word(), 
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
        ]);

        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'username' => 'employee_' . $this->faker->unique()->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user', // Assumindo que existe uma role 'user' no sistema
            'username' => 'regular_' . $this->faker->unique()->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Teste de Criação de Usuários
     */

    #[Test]
    public function an_admin_can_create_a_new_user(): void
    {
        Sanctum::actingAs($this->adminUser);

        $userData = [
            'name' => 'Novo Admin Teste',
            'username' => 'novo_admin_test',
            'email' => 'novo.admin@test.com',
            'password' => 'novasenha123',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201) // 201 Created
                 ->assertJson(['message' => 'Usuário registrado com sucesso!']);

        $this->assertDatabaseHas('users', [
            'username' => 'novo_admin_test',
            'email' => 'novo.admin@test.com',
            'role' => 'admin',
        ]);
    }

    #[Test]
    public function an_employee_cannot_create_a_new_user(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $userData = [
            'name' => 'Novo Funcionario Teste',
            'username' => 'novo_funcionario_test',
            'email' => 'novo.funcionario@test.com',
            'password' => 'senha123',
            'role' => 'employee',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(403); // 403 Forbidden (espera que a Gate 'create-users' bloqueie)
        $this->assertDatabaseMissing('users', ['username' => 'novo_funcionario_test']);
    }

    #[Test]
    public function it_requires_valid_data_to_create_a_user(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/users', [
            'name' => '', // Nome faltando (required)
            'username' => '', // Username faltando (required)
            'email' => 'invalid-email', // Email inválido
            'password' => '123', // Senha muito curta (min:6)
            'role' => 'invalid_role', // Role inválida
        ]);

        $response->assertStatus(422) // 422 Unprocessable Entity
                 ->assertJsonValidationErrors(['name', 'username', 'email', 'password', 'role']);
    }

    #[Test]
    public function it_cannot_create_user_with_duplicate_username_or_email(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Criar usuário existente para forçar a duplicação
        User::factory()->create(['username' => 'existinguser', 'email' => 'existing@user.com']);

        // Tentar criar com username duplicado (deve retornar 422)
        $response = $this->postJson('/api/users', [
            'name' => 'Teste Duplicado',
            'username' => 'existinguser', // Este username já existe
            'email' => $this->faker->unique()->safeEmail(), // Email único para esta tentativa
            'password' => 'password123',
            'role' => 'employee',
        ]);
        $response->assertStatus(422) // Agora espera 422, pois a validação 'unique' do Laravel retorna 422
                 ->assertJsonValidationErrors(['username']); 

        // Tentar criar com email duplicado (deve retornar 422)
        $response = $this->postJson('/api/users', [
            'name' => 'Teste Duplicado',
            'username' => $this->faker->unique()->word(), // Username único para esta tentativa
            'email' => 'existing@user.com', // Este email já existe
            'password' => 'password123',
            'role' => 'employee',
        ]);
        $response->assertStatus(422) // Agora espera 422
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Teste de Listagem de Usuários
     */

    #[Test]
    public function an_admin_can_list_users(): void
    {
        Sanctum::actingAs($this->adminUser);
        User::factory()->count(5)->create(); // Criar 5 usuários aleatórios

        $response = $this->getJson('/api/users');

        // Contagem: 3 usuários base (admin, employee, regular) + 5 criados neste teste = 8
        $response->assertStatus(200)
                 ->assertJsonCount(8); 
    }

    #[Test]
    public function an_employee_can_list_users(): void
    {
        Sanctum::actingAs($this->employeeUser);
        User::factory()->count(2)->create(); // Criar 2 usuários aleatórios

        // Contagem: 3 usuários base (admin, employee, regular) + 2 criados neste teste = 5
        $response = $this->getJson('/api/users');

        $response->assertStatus(200) // Espera 200, pois a Gate 'view-users' permite a employees
                 ->assertJsonCount(5); 
    }

    #[Test]
    public function a_regular_user_cannot_list_users(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/users');

        $response->assertStatus(403); // 403 Forbidden (espera que a Gate 'view-users' bloqueie regular users)
    }

    /**
     * Teste de Visualização de Usuário Específico
     */

    #[Test]
    public function an_admin_can_show_a_specific_user(): void
    {
        Sanctum::actingAs($this->adminUser);
        $targetUser = User::factory()->create();

        $response = $this->getJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $targetUser->id,
                     'username' => $targetUser->username,
                     'role' => $targetUser->role,
                 ]);
    }

    #[Test]
    public function an_employee_can_show_a_specific_user(): void
    {
        Sanctum::actingAs($this->employeeUser);
        $targetUser = User::factory()->create();

        $response = $this->getJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200); // Espera 200, pois a Gate 'view-users' permite a employees
        $response->assertJson([ // Adicionado asserção JSON para verificar o conteúdo
            'id' => $targetUser->id,
            'username' => $targetUser->username,
            'role' => $targetUser->role,
        ]);
    }

    #[Test]
    public function it_returns_404_if_user_to_show_is_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);
        $response = $this->getJson('/api/users/9999'); // ID que não existe
        $response->assertStatus(404);
    }

    /**
     * Teste de Atualização de Usuários
     */

    #[Test]
    public function an_admin_can_update_a_user(): void
    {
        Sanctum::actingAs($this->adminUser);
        $userToUpdate = User::factory()->create(['role' => 'employee']);

        $updatedData = [
            'name' => 'Eduardo',
            'username' => 'Eduardo Padilha',
            'email' => 'albergue.admin@email.com',
            'password' => '123456',
            'role' => 'admin', // Alterando a role
        ];

        $response = $this->putJson('/api/users/' . $userToUpdate->id, $updatedData);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Usuário atualizado com sucesso!']);

        $this->assertDatabaseHas('users', [
            'id' => $userToUpdate->id,
            'name' => 'Eduardo',
            'username' => 'Eduardo Padilha',
            'email' => 'albergue.admin@email.com',
            'role' => 'admin',
        ]);
        $this->assertTrue(Hash::check('123456', $userToUpdate->fresh()->password));
    }
    
    #[Test]
    public function an_admin_can_update_a_user_without_changing_password(): void
    {
        Sanctum::actingAs($this->adminUser);
        $oldPassword = 'currentpassword';
        $userToUpdate = User::factory()->create([
            'role' => 'employee',
            'password' => bcrypt($oldPassword)
        ]);

        $updatedData = [
            'name' => 'Hermano',
            'username' => 'username_sem_senha',
            'email' => 'sem.senha@email.com',
            'password' => null, // Não enviar nova senha
            'role' => 'employee', // A role é obrigatória no update
        ];

        $response = $this->putJson('/api/users/' . $userToUpdate->id, $updatedData);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Usuário atualizado com sucesso!']);

        $this->assertDatabaseHas('users', [
            'id' => $userToUpdate->id,
            'name' => 'Hermano',
            'username' => 'username_sem_senha',
            'email' => 'sem.senha@email.com',
        ]);
        // Garante que a senha antiga ainda é válida
        $this->assertTrue(Hash::check($oldPassword, $userToUpdate->fresh()->password));
    }

    #[Test]
    public function an_employee_cannot_update_another_user(): void
    {
        Sanctum::actingAs($this->employeeUser);
        $userToUpdate = User::factory()->create(['role' => 'admin']);

        // Enviar dados válidos, mas a ação deve ser bloqueada pela Gate 'update-users'
        $response = $this->putJson('/api/users/' . $userToUpdate->id, [
            'name' => 'Tentativa Funcionario',
            'username' => $userToUpdate->username, // Manter o username existente
            'email' => $userToUpdate->email, // Manter o email existente
            'password' => null, 
            'role' => $userToUpdate->role, // Manter a role existente
        ]);
        $response->assertStatus(403); // 403 Forbidden (espera que a Gate 'update-users' bloqueie)
    }
    
    #[Test]
    public function it_cannot_update_user_to_duplicate_username_or_email(): void
    {
        Sanctum::actingAs($this->adminUser);
        
        $existingUser = User::factory()->create(['username' => 'user_existente', 'email' => 'email@existente.com']);
        $userToUpdate = User::factory()->create(['username' => 'user_a_atualizar', 'email' => 'email_a_atualizar@com']);

        // Tentar atualizar para username duplicado (deve retornar 422)
        $response = $this->putJson('/api/users/' . $userToUpdate->id, [
            'name' => 'Marcos',
            'username' => 'user_existente', // Este username já existe
            'email' => $userToUpdate->email, // Manter o email atual
            'password' => null,
            'role' => $userToUpdate->role, // Manter a role atual
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username']);

        // Tentar atualizar para email duplicado (deve retornar 422)
        $response = $this->putJson('/api/users/' . $userToUpdate->id, [
            'name' => 'Marcos',
            'username' => $userToUpdate->username, // Manter o username atual
            'email' => 'email@existente.com', // Este email já existe
            'password' => null,
            'role' => $userToUpdate->role, // Manter a role atual
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Teste de Deleção de Usuários
     */

    #[Test]
    public function an_admin_can_delete_a_user(): void
    {
        Sanctum::actingAs($this->adminUser);
        $userToDelete = User::factory()->create(['role' => 'employee']);

        $response = $this->deleteJson('/api/users/' . $userToDelete->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    #[Test]
    public function an_admin_cannot_delete_their_own_account(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson('/api/users/' . $this->adminUser->id);

        $response->assertStatus(403) // 403 Forbidden
                 ->assertJson(['message' => 'You cannot delete your own account']);
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    #[Test]
    public function an_employee_cannot_delete_a_user(): void
    {
        Sanctum::actingAs($this->employeeUser);
        $userToDelete = User::factory()->create();

        $response = $this->deleteJson('/api/users/' . $userToDelete->id);

        $response->assertStatus(403); // 403 Forbidden (espera que a Gate 'delete-users' bloqueie)
        $this->assertDatabaseHas('users', ['id' => $userToDelete->id]);
    }

    #[Test]
    public function it_returns_404_if_user_to_delete_is_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);
        $response = $this->deleteJson('/api/users/9999'); // ID que não existe
        $response->assertStatus(404);
    }
}