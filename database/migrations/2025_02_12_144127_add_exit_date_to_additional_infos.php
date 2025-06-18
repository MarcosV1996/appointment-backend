<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('additional_infos', function (Blueprint $table) {
            $table->date('exit_date')->nullable()->after('stay_duration'); 
        });
    }
    
    public function down()
    {
        Schema::table('additional_infos', function (Blueprint $table) {
            $table->dropColumn('exit_date'); 
        });
    }
    
};
