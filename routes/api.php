<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Models\User;

// =============================
// Rotas de Autenticação
// =============================

// Login e Registro
Route::middleware([EnsureFrontendRequestsAreStateful::class])->post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Logout
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Usuário autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =============================
// Rotas de Recuperação de Senha
// =============================

// Enviar e-mail com link de recuperação de senha
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// Redefinir senha ao clicar no link do e-mail
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// **Alternativa Manual de Solicitação de Redefinição de Senha**
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required']);

    $user = User::where('email', $request->email)->firstOrFail();
    Password::sendResetLink(['email' => $user->email]);

    return response()->json(['message' => 'Link de redefinição de senha enviado.'], 200);
});

// **Alternativa Manual para Redefinir Senha**
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => 'required',
        'token' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = User::where('email', $request->email)->firstOrFail();

    $status = Password::reset(
        [
            'email' => $user->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token,
        ],
        function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Senha redefinida com sucesso.'], 200)
        : response()->json(['message' => 'Erro ao redefinir senha.'], 500);
})->name('password.update');

// =============================
// Rotas de Usuários
// =============================

// Listar todos os usuários
Route::get('/users', [UserController::class, 'index']);

// Exibir usuário específico
Route::get('/users/{id}', [UserController::class, 'show']);

// Atualizar usuário
Route::put('/users/{id}', [UserController::class, 'update']);

// Excluir usuário
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Upload de foto do usuário
Route::post('/users/{id}/upload-photo', [UserController::class, 'uploadPhoto']);
Route::post('/users/{id}/photo', [UserController::class, 'uploadPhoto']);

// =============================
// Rotas de Agendamentos
// =============================

// Listar agendamentos
Route::get('/appointments', [AppointmentController::class, 'index']);

// Exibir detalhes de um agendamento
Route::get('/appointments/{id}', [AppointmentController::class, 'show']);

// Criar agendamento
Route::post('/appointments', [AppointmentController::class, 'store']);

// Atualizar agendamento
Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);

// Excluir agendamento
Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

// Ocultar agendamento
Route::put('/appointments/{id}/hide', [AppointmentController::class, 'hide']);

// =============================
// Rotas de Camas Disponíveis
// =============================

Route::get('/available-beds', [AppointmentController::class, 'getAvailableBeds']);
Route::get('/appointments/available-beds', [AppointmentController::class, 'getAvailableBeds']);

// =============================
// Rotas de Relatórios
// =============================

Route::get('/reports', [AppointmentController::class, 'getReports']);

// Contagem de camas
Route::get('/bed-counts', [AppointmentController::class, 'getBedCounts']);

// =============================
// Rotas Protegidas (Requer Autenticação)
// =============================

Route::middleware('auth:sanctum')->get('/protected-route', function () {
    return response()->json(['message' => 'Acesso autorizado']);
});
