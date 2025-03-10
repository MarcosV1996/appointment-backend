<?php

namespace App\Models;

<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Contracts\Auth\MustVerifyEmail;
=======
>>>>>>> Initial commit - Laravel backend
=======
use Illuminate\Contracts\Auth\MustVerifyEmail;
>>>>>>> Atualização de Testes
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Auth\Notifications\ResetPassword;

class User extends Authenticatable
=======
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
>>>>>>> Initial commit - Laravel backend
=======
use Illuminate\Auth\Notifications\ResetPassword;

class User extends Authenticatable
>>>>>>> Atualização de Testes
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Atualização de Testes
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
 
    protected $fillable = ['name', 'username', 'email', 'password', 'role'];


<<<<<<< HEAD
=======
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'role',
    ];
>>>>>>> Initial commit - Laravel backend
=======
>>>>>>> Atualização de Testes

    /**
     * Os atributos que devem ser ocultados para arrays.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * @var array<int, string>
=======
     * @var array
>>>>>>> Initial commit - Laravel backend
=======
     * @var array<int, string>
>>>>>>> Atualização de Testes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Atualização de Testes
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
<<<<<<< HEAD
=======
     * Define o identificador de autenticação para usar 'username' em vez de 'email'.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Retorna o identificador do JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retorna um array de atributos personalizados do JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
>>>>>>> Initial commit - Laravel backend
=======
>>>>>>> Atualização de Testes
}
