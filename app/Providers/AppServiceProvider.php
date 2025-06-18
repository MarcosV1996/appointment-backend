<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Validação customizada de CPF
        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            $cpf = preg_replace('/[^0-9]/', '', $value);

            if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
                return false;
            }

            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    return false;
                }
            }

            return true;
        });

        // Definindo políticas de autorização
        Gate::before(function ($user, $ability) {
            // Debug: Verificar permissões
            \Log::info("Verificando permissão para {$ability}", [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);

            // Permite tudo para admin (TESTE - remova depois de confirmar)
            if ($user->role === 'admin') {
                return true;
            }
        });

        // Política padrão para usuários
        Gate::define('delete-user', function (User $authUser, User $targetUser) {
            return $authUser->role === 'admin' && 
                   $authUser->id !== $targetUser->id;
        });

        // Registra a política de usuário
        Gate::policy(User::class, \App\Policies\UserPolicy::class);
    }
}