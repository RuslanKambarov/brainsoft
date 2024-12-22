<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('last_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id')->references('id')->on('objects')->onDelete('cascade');
            $table->float('outside_t');
            $table->float('back_t');
            $table->float('direct_t');
            $table->float('pressure');
            $table->tinyInteger('status');
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
        Schema::dropIfExists('last_data');
    }
}
