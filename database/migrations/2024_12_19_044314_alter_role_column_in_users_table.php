<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRoleColumnInUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modificar a coluna role para aceitar 'user'
            $table->enum('role', ['admin', 'employee', 'user'])->default('user')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverter a coluna role para os valores anteriores
            $table->enum('role', ['admin', 'employee'])->default('employee')->change();
        });
    }
}
