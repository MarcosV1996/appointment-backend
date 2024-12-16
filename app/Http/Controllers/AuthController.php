<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
<<<<<<< HEAD
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
=======
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
    
        $user = User::where('username', $credentials['username'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }
    
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id, 
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
        
>>>>>>> Initial commit - Laravel backend
    }
    
    public function logout(Request $request)
    {
<<<<<<< HEAD
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
=======
        // Logout do usuário usando a guarda 'api'
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
>>>>>>> Initial commit - Laravel backend
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string'
        ]);

        $user = User::create([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return response()->json(['message' => 'Usuário registrado com sucesso!', 'user' => $user], 201);
    }
<<<<<<< HEAD
    
}
=======
}

>>>>>>> Initial commit - Laravel backend
