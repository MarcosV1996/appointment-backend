<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('last_name'); 
            $table->string('cpf'); 
            $table->string('mother_name'); 
            $table->date('date'); 
            $table->time('time'); 

           
            $table->string('state')->nullable();
            $table->string('city')->nullable();

            $table->string('phone')->nullable();

            $table->boolean('foreign_country')->default(false); 
            $table->boolean('no_phone')->default(false); 
            $table->string('gender')->nullable(); 
            $table->date('arrival_date')->nullable();
            $table->text('observation')->nullable();
            $table->string('photo')->nullable(); 

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
