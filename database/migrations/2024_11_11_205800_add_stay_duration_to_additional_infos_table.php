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
        // Verifica se a coluna não existe antes de adicionar
        if (!Schema::hasColumn('additional_infos', 'stay_duration')) {
            Schema::table('additional_infos', function (Blueprint $table) {
                $table->integer('stay_duration')->nullable();
            });
        }
    }
    
    public function down()
    {
        Schema::table('additional_infos', function (Blueprint $table) {
            $table->dropColumn('stay_duration');
        });
    }
};
