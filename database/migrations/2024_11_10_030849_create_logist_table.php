<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id')->references('id')->on('objects')->onDelete('cascade');
            $table->integer('label');
            $table->float('amount');
            $table->integer('logist')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('date');          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logist');
    }
}
