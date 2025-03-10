<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedsTable extends Migration
{
    public function up()
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->unsignedBigInteger('room_id'); // Chave estrangeira para rooms
            $table->string('bed_number'); // Número da cama no quarto (Ex: Bed 1)
            $table->boolean('is_available')->default(true); // Se a cama está disponível ou não
            $table->timestamps();

            // Chave estrangeira para rooms
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('beds');
    }
}
