<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('daily'); 
                $table->date('report_date')->default(now()); 
                $table->json('data')->nullable();
                $table->text('summary')->nullable();
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
