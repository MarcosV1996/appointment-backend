<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestSessionController;
use App\Http\Controllers\HomeController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// =============================
// Rotas de autenticação
// =============================

// Rota para obter o token CSRF para proteção de formulários
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Login e registro
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Rota de verificação de e-mail
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Marca o e-mail como verificado

    // Dispara manualmente o evento de verificação
    event(new Verified($request->user()));

    return redirect(config('app.frontend_url') . '/dashboard?verified=1');
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// Páginas de login/logout
Route::get('/login', function () {
    return view('auth.login');
})->name('login'); // Página de login

Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // Endpoint para logout

// =============================
// Rotas protegidas
// =============================

Route::middleware('auth:sanctum')->group(function () {
    // Rota de usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Página inicial após o login
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// =============================
// Páginas informativas
// =============================

Route::get('/', function () {
    return view('welcome');
})->name('welcome'); // Página inicial

Route::get('/about', function () {
    return view('about');
})->name('about'); // Página Sobre

Route::get('/contato', function () {
    return view('contato');
})->name('contato'); // Página de Contato

Route::get('/privacidade', function () {
    return view('privacidade');
})->name('privacidade'); // Política de Privacidade

// =============================
// Páginas de agendamentos e relatórios
// =============================

Route::get('/agendamentos', function () {
    return view('agendamentos');
})->name('agendamentos'); // Página de Agendamentos

Route::get('/relatorios', function () {
    return view('relatorios');
})->name('relatorios'); // Página de Relatórios

// =============================
// Testes
// =============================

Route::get('/test-session', [TestSessionController::class, 'test'])->name('test-session');

// =============================
// Grupo de rotas protegidas por middleware CORS
// =============================

Route::middleware([\App\Http\Middleware\CorsMiddleware::class])->group(function () {
    // Adicione suas rotas protegidas por CORS aqui
});
