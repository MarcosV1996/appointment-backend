<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    public function store(Request $request)
    {
<<<<<<< HEAD
        // Método para criar um novo usuário (exemplo)
    }

    // Método para fazer upload de foto do usuário
    public function uploadPhoto(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Remover foto antiga, se houver
=======
        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string'
        ]);
    
        // Verifica se o username já existe
        if (User::where('username', $request->username)->exists()) {
            return response()->json(['message' => 'O nome de usuário já está cadastrado!'], 409);
        }
    
        // Verifica se o e-mail já existe
        if ($request->email && User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'O e-mail já está cadastrado!'], 409);
        }
    
        // Criar novo usuário
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email ?? null,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);
    
        return response()->json(['message' => 'Usuário registrado com sucesso!', 'user' => $user], 201);
    }
    
    // Método para fazer upload de foto do usuário
    public function uploadPhoto(Request $request, $id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
    
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Remove a foto antiga, se existir
>>>>>>> Atualização de Testes
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
    
<<<<<<< HEAD
            // Salvar a nova foto
=======
            // Salva a nova foto
>>>>>>> Atualização de Testes
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
            $user->save();
    
<<<<<<< HEAD
            // Retornar o novo caminho da foto com a URL completa
            return response()->json(['photo' => asset('storage/' . $photoPath)], 200);
        }
    
        return response()->json(['message' => 'Erro ao fazer upload da foto.'], 500);
    }
    

    
    public function show($id)
{
=======
            return response()->json(['photo' => asset('storage/' . $photoPath)], 200);
        }
    
        return response()->json(['message' => 'Erro ao fazer upload da foto.'], 400);
    }
    
    
    public function show($id)
  {
>>>>>>> Atualização de Testes
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado.'], 404);
    }

    return response()->json([
        'id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
<<<<<<< HEAD
        'photo' => $user->photo // Retorna o caminho da foto se existir
    ]);
=======
        'photo' => $user->photo 
    ]);
  }

  public function index()
  {
      $users = User::all(['id', 'name', 'username', 'email', 'role']);
  
      return response()->json($users);
  }
  
  public function destroy($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado'], 404);
    }
    $user->delete();
    return response()->json(['message' => 'Usuário excluído com sucesso'], 200);
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
        'role' => 'required|string'
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
>>>>>>> Atualização de Testes
}

}
