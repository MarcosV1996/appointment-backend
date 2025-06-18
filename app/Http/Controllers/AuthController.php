<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ], [
            'username.required' => 'O nome de usuário é obrigatório',
            'password.required' => 'A senha é obrigatória'
        ]);
    
        $credentials = $request->only('username', 'password');
    
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }
    
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Login bem-sucedido',
            'token' => $token,
            'user' => $user
        ]);
    }

   
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            $request->user()->tokens()->delete();
            Log::info('Logout successful', ['user_id' => $user->id]);
        } else {
            Log::warning('Logout attempted with invalid user');
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

   
    public function register(Request $request)
    {
        Log::info('Registration attempt started');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,employee,user',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            Log::warning('Registration validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            Log::info('Profile photo uploaded', ['path' => $photoPath]);
        }

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'username' => strip_tags($request->username),
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'photo' => $photoPath
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
            'photo_path' => $photoPath
        ];

        Log::info('Registration successful', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $userData
        ], 201);
    }

    
    public function getUser(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            'photo_path' => $user->photo
        ];

        return response()->json([
            'user' => $userData
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $photoPath = $request->file('photo')->store('profile-photos', 'public');
        $user->photo = $photoPath;
        $user->save();

        $photoUrl = asset('storage/' . $photoPath);

        Log::info('Profile photo updated', [
            'user_id' => $user->id,
            'photo_path' => $photoPath
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => $photoUrl,
            'photo_path' => $photoPath
        ]);
    }
     public function __construct()
    {
        // Aplica o middleware apenas para deletar
        $this->middleware('can:delete,user')->only('destroy');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Acesso negado: Você não é administrador');
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuário deletado com sucesso'
        ]);
    }
}