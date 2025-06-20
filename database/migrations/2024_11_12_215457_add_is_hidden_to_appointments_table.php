<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('isHidden')->default(false);
        });
    }
    
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            
        });
    }
};
