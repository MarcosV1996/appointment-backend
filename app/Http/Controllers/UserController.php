<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Método para criar um novo usuário (exemplo)
    }

    // Método para fazer upload de foto do usuário
    public function uploadPhoto(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Remover foto antiga, se houver
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
    
            // Salvar a nova foto
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
            $user->save();
    
            // Retornar o novo caminho da foto com a URL completa
            return response()->json(['photo' => asset('storage/' . $photoPath)], 200);
        }
    
        return response()->json(['message' => 'Erro ao fazer upload da foto.'], 500);
    }
    

    
    public function show($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado.'], 404);
    }

    return response()->json([
        'id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'photo' => $user->photo // Retorna o caminho da foto se existir
    ]);
}

}
