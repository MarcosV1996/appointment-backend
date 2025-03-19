<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalInfosTable extends Migration
{
    public function up()
    {
        Schema::create('additional_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id'); 
            $table->string('ethnicity')->nullable(); 
            $table->text('addictions')->nullable(); 
            $table->boolean('is_accompanied')->default(false); 
            $table->text('benefits')->nullable(); 
            $table->boolean('is_lactating')->default(false); 
            $table->boolean('has_disability')->default(false); 
            $table->unsignedBigInteger('room_id')->nullable(); 
            $table->unsignedBigInteger('bed_id')->nullable(); 
            $table->text('reason_for_accommodation')->nullable(); 
            $table->boolean('has_religion')->default(false); 
            $table->string('religion')->nullable(); 
            $table->boolean('has_chronic_disease')->default(false); 
            $table->string('chronic_disease')->nullable(); 
            $table->string('education_level')->nullable(); 
            $table->string('nationality')->default('Brasileiro'); 
            $table->integer('stay_duration')->default(1); 
            $table->timestamps(); // Definido apenas uma vez

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('additional_infos');
    }
}
