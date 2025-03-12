<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('MeuToken')->plainTextToken;

            return response()->json([
                'message' => 'Login bem-sucedido!',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Credenciais inválidas'], 401);
    }
    
    public function logout(Request $request)
{
    $user = Auth::user();

    if ($user) {
        $user->tokens()->delete(); // Remove todos os tokens do usuário autenticado
    }

    return response()->json(['message' => 'Logout realizado com sucesso'], 204);
}

public function register(Request $request)
{
    Log::info('Dados recebidos para registro:', $request->all()); 

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users',
        'username' => 'required|string|max:255|unique:users',
        'password' => 'required|string|min:6',
        'role' => 'required|string'
    ]);

    $validatedData['username'] = strip_tags($validatedData['username']);

    Log::info('Dados validados:', $validatedData); // Verifica se os dados foram validados corretamente

    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'] ?? null, 
        'username' => $validatedData['username'],
        'password' => Hash::make($validatedData['password']),
        'role' => $validatedData['role'],
    ]);

    Log::info('Usuário registrado:', $user->toArray()); 

    return response()->json(['message' => 'Usuário registrado com sucesso!', 'user' => $user], 201);
}

}
