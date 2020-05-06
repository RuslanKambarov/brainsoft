<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objectcards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id');
            $table->double('outside_t', 8, 2);
            $table->double('direct_t', 8, 2);
            $table->double('back_t', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('objectcards');
    }
}
