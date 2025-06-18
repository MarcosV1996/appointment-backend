<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/appointments/available-beds', [AppointmentController::class, 'getAvailableBeds']);

Route::options('/{any}', function () {
    return response()->noContent();
})->where('any', '.*');


Route::group(['middleware' => ['api']], function () {
    // CSRF Cookie Endpoint - MANTIDO AQUI porque o frontend o chama com /api/.
    Route::get('/sanctum/csrf-cookie', function (Request $request) {
        return response()->noContent()
            ->withCookie(
                'XSRF-TOKEN',
                csrf_token(),
                config('session.lifetime'),
                '/',
                null,
                config('session.secure'),
                false
            );
    });

    // Login e Registro para estarem sob o middleware 'api' 
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Rotas de recuperação de senha (públicas)
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

    // Rotas públicas de agendamentos
    Route::get('/available-beds', [AppointmentController::class, 'getAvailableBeds']);
    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware(['throttle:5,1']);
});
// =================================================================================


// === ROTAS AUTENTICADAS PELO LARAVEL SANCTUM ===
// Este grupo de middleware garante que o usuário esteja logado via Sanctum.
Route::middleware([
    'auth:sanctum', // Middleware de autenticação do Sanctum
    'throttle:60,1' // Limitação de taxa para usuários autenticados
])->group(function () {
    // Rota para o usuário autenticado atual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Grupo de rotas para recursos de usuários (prefixadas com 'users')
    Route::prefix('users')->group(function () {
        Route::post('/', [UserController::class, 'store'])->middleware('can:create-users'); 
        Route::get('/', [UserController::class, 'index'])->middleware('can:view-users');
        Route::get('/{user}', [UserController::class, 'show'])->middleware('can:view-users');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('can:update-users');
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('can:delete-users'); 
            
        Route::post('/{user}/photo', [UserController::class, 'uploadPhoto'])
            ->name('users.photo.upload');
        Route::get('/{user}/photo', function ($userId) {
            $user = \App\Models\User::findOrFail($userId);
            if (!$user->photo) {
                abort(404, 'Foto não encontrada');
            }
            $path = storage_path('app/public/' . $user->photo);
            if (!file_exists($path)) {
                abort(404);
            }
            return response()->file($path);
        })->name('users.photo.show');
    });

    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::get('/{id}', [AppointmentController::class, 'show']); 
        Route::post('/', [AppointmentController::class, 'store']); 
        Route::put('/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/{id}', [AppointmentController::class, 'destroy']);
        Route::put('/{id}/hide', [AppointmentController::class, 'hide']);
    });

    Route::prefix('reports')->group(function () {
        Route::get('/', [AppointmentController::class, 'getReports']); 
        Route::get('/bed-counts', [AppointmentController::class, 'getBedCounts']); 
        Route::post('/save', [AppointmentController::class, 'saveReport']); 
    });
});


Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    if (!$request->hasValidSignature()) {
        abort(403, 'Invalid verification link');
    }

    $user = \App\Models\User::find($request->route('id')); 
    if (!$user || !hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect(env('FRONTEND_URL', 'http://localhost:8080') . '/login?verified=1');
    }

    $user->markEmailAsVerified();
    
    return redirect(env('FRONTEND_URL', 'http://localhost:8080') . '/login?verified=1');
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');


Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }
    
    return response()->file($fullPath);
})->where('path', '.*');