<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User; // Importe o modelo User

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class, // Certifique-se de que UserPolicy existe se você for usá-la
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // ==== DEFINIÇÃO DAS GATES ====
        
        // Gate para criar usuários: Apenas Admins podem criar.
        Gate::define('create-users', function (User $user) {
            return $user->isAdmin();
        });

        // Gate para listar/visualizar usuários: Admins e Employees podem visualizar.
        Gate::define('view-users', function (User $user) {
            return $user->isAdmin() || $user->isEmployee();
        });

        // Gate para atualizar usuários: Apenas Admins podem atualizar.
        Gate::define('update-users', function (User $user) {
            return $user->isAdmin();
        });

        // Gate para deletar usuários: Apenas Admins podem deletar.
        Gate::define('delete-users', function (User $user) {
            return $user->isAdmin();
        });

    }
}