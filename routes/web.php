<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestSessionController;
use App\Http\Controllers\HomeController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


// Rota para obter o token CSRF para proteção de formulários
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
