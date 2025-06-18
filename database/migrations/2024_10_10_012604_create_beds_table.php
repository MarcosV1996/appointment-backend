<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedsTable extends Migration
{
    public function up()
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id'); 
            $table->string('bed_number'); 
            $table->boolean('is_available')->default(true); 
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('beds');
    }
}
