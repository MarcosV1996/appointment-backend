<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; // Certifique-se de que essa linha está presente
use Illuminate\Support\Facades\Validator;

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
    }
}
