<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->string('name'); // Nome do quarto (Ex: Room 101)
            $table->integer('capacity'); // Capacidade total de camas
            $table->integer('occupied_beds')->default(0); // Número de camas ocupadas
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
