<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    public function test(Request $request)
    {
        if (!$request->session()->has('test_session')) {
            $request->session()->put('test_session', 'Sessão criada com sucesso!');
        }

        $sessionValue = $request->session()->get('test_session');

        return response()->json(['message' => $sessionValue]);
    }
}
