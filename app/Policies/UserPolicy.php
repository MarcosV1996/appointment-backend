<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
   public function delete(User $user, User $model)
{
    // Permite apenas se for admin e não estiver deletando a si mesmo
    return $user->role === 'admin' && $user->id !== $model->id;
}
}