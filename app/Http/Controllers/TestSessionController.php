<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    public function test(Request $request)
    {
        // Inicia uma sessão ou retoma a existente
        if (!$request->session()->has('test_session')) {
            // Armazena um valor na sessão
            $request->session()->put('test_session', 'Sessão criada com sucesso!');
        }

        // Recupera o valor da sessão
        $sessionValue = $request->session()->get('test_session');

        // Retorna o valor da sessão como resposta
        return response()->json(['message' => $sessionValue]);
    }
}
