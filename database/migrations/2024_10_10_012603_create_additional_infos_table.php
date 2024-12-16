<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalInfosTable extends Migration
{
    public function up()
    {
        Schema::create('additional_infos', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->unsignedBigInteger('appointment_id'); // Chave estrangeira para appointments
            $table->string('ethnicity')->nullable(); // Etnia
            $table->text('addictions')->nullable(); // Vícios
            $table->boolean('is_accompanied')->default(false); // Se está acompanhado
            $table->text('benefits')->nullable(); // Benefícios
            $table->boolean('is_lactating')->default(false); // Se é lactante
            $table->boolean('has_disability')->default(false); // Se possui deficiência

            // Colunas para room e bed
            $table->unsignedBigInteger('room_id')->nullable(); // Chave estrangeira para rooms
            $table->unsignedBigInteger('bed_id')->nullable(); // Chave estrangeira para beds

            // Novas colunas adicionais
            $table->text('reason_for_accommodation')->nullable(); // Motivo do acolhimento
            $table->boolean('has_religion')->default(false); // Se possui religião
            $table->string('religion')->nullable(); // Qual religião, se aplicável
            $table->boolean('has_chronic_disease')->default(false); // Se possui doença crônica
            $table->string('chronic_disease')->nullable(); // Qual doença crônica, se aplicável
            $table->string('education_level')->nullable(); // Escolaridade
            $table->string('nationality')->default('Brasileiro'); // Nacionalidade, default 'Brasileiro'
<<<<<<< HEAD

            // Duração da estadia (foi movido para cá)
            $table->integer('stay_duration')->default(1); // Duração da estadia em dias

            $table->timestamps();

=======
            $table->integer('stay_duration')->default(1); // Duração da estadia em dias

            $table->timestamps();
            
>>>>>>> Initial commit - Laravel backend
            // Definindo chaves estrangeiras
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('set null');
        });
    }

<<<<<<< HEAD

=======
>>>>>>> Initial commit - Laravel backend
    public function down()
    {
        Schema::dropIfExists('additional_infos');
    }
}
