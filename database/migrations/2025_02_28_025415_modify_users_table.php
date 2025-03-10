<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable(); // Email antes da senha
            $table->string('password');
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

        // Copiar dados antigos para a nova estrutura
        DB::statement('INSERT INTO users_temp (id, username, email, password, role, remember_token, created_at, updated_at)
            SELECT id, username, email, password, role, remember_token, created_at, updated_at FROM users');

        // Remover a tabela antiga e renomear a nova
        Schema::dropIfExists('users');
        Schema::rename('users_temp', 'users');
    }

    public function down()
    {
        Schema::create('users_old', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

        // Copiar de volta para a estrutura antiga
        DB::statement('INSERT INTO users_old (id, username, password, role, remember_token, created_at, updated_at)
            SELECT id, username, password, role, remember_token, created_at, updated_at FROM users');

        Schema::dropIfExists('users');
        Schema::rename('users_old', 'users');
    }
};
