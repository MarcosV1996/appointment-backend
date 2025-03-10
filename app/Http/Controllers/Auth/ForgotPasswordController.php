<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;
=======
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Valida o email recebido
        $request->validate(['email' => 'required|email']);

        // Verifica se o usuário com esse email existe e é administrador
        $user = User::where('email', $request->email)->first();

        if ($user && $user->role === 'admin') {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json(['message' => 'Link de recuperação enviado com sucesso!'], 200);
            }

            return response()->json(['message' => 'Erro ao enviar link de recuperação.'], 500);
        }

        return response()->json(['message' => 'Recuperação de senha apenas para administradores.'], 403);
    }
>>>>>>> Atualização de Testes
}
