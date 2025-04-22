<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('daily'); // Valor padrão adicionado
                $table->date('report_date')->default(now()); // Valor padrão adicionado
                $table->json('data')->nullable(); // Permite nulo inicialmente
                $table->text('summary')->nullable(); // Permite nulo inicialmente
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('reports');
    }
   
};
