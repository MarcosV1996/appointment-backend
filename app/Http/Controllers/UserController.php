<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Validation\Rule; // <-- Importe a classe Rule

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users', // Validação 'unique' do Laravel
            'email' => 'nullable|email|unique:users',             // Validação 'unique' do Laravel
            'password' => 'required|string|min:6',
            // CORREÇÃO: Adicionar regra 'in' para validar a role
            'role' => ['required', 'string', Rule::in(['admin', 'employee', 'user'])], // <-- MUDANÇA AQUI
        ]);
    
        // REMOVIDOS OS BLOCOS 'if (User::where(...)->exists())'
        // A validação 'unique:users' acima já lida com isso e retorna um status 422
        // com as mensagens de erro padrão, que é o esperado pelos testes agora.
        
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email ?? null,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);
    
        return response()->json(['message' => 'Usuário registrado com sucesso!', 'user' => $user], 201);
    }
    
    public function uploadPhoto(Request $request, $id)
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = User::findOrFail($id);

        try {
            // Remove foto antiga se existir
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Armazena a nova foto
            $path = $request->file('photo')->store('user-photos', 'public');
            $user->photo = $path;
            $user->save();

            return response()->json([
                'photo' => Storage::url($path),
                'message' => 'Foto atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Upload error: '.$e->getMessage());
            return response()->json([
                'message' => 'Erro ao processar upload',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        \Log::info("Acessando usuário ID: {$id}");
        try {
            $user = User::find($id);
            
            if (!$user) {
                \Log::warning("Usuário não encontrado ID: {$id}");
                return response()->json(['message' => 'Usuário não encontrado.'], 404);
            }
            
            \Log::debug("Dados do usuário:", $user->toArray());
            return response()->json([
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'photo' => $user->photo 
            ]);
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar usuário ID: {$id}", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }

    // Nota: O método 'handle' geralmente é um middleware, não um método de controller.
    // Se você o copiou de um middleware, ele não pertence aqui e pode ser removido
    // ou movido para App\Http\Middleware.
    /*
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;
        
        \Log::info("{$request->method()} {$request->fullUrl()} - {$duration}ms");
        return $response;
    }
    */

    public function index()
    {
        \Log::info('Acessando lista de usuários');
        try {
            $users = User::all(['id', 'name', 'username', 'email', 'role']);
            \Log::info('Usuários encontrados: ' . $users->count());
            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar usuários: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // As verificações de autorização devem idealmente ser feitas por Policies ou Gates
        // no middleware 'can' na rota ou no AuthServiceProvider.php.
        // No entanto, como seu teste já passou com a lógica aqui, vou manter,
        // mas note que 'if (!auth()->user()->isAdmin())' é redundante com a Gate.
        
        if (!auth()->user()->isAdmin()) { // Este bloco é importante se a Gate não pegar todos os casos.
            // Isso aqui seria pego pela Gate 'delete-users', mas manter para clareza se necessário.
            abort(403, 'Unauthorized action.'); 
        }

        if (auth()->id() == $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        if (!auth()->user()->hasRole('admin')) { // Isso é redundante com a primeira verificação isAdmin()
            return response()->json([
                'message' => 'You do not have permission to delete users'
            ], 403);
        }

        $user->delete();
        
        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$id,
            'email' => 'nullable|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            // CORREÇÃO: Adicionar regra 'in' para validar a role também no update
            'role' => ['required', 'string', Rule::in(['admin', 'employee', 'user'])], // <-- MUDANÇA AQUI
        ]);

        $user->name = $validatedData['name'];
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'] ?? null;

        if (!empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        $user->role = $validatedData['role'];
        $user->save();

        return response()->json(['message' => 'Usuário atualizado com sucesso!', 'user' => $user]);
    }
}