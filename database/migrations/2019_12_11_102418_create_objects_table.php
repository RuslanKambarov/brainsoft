<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owen_id');
            $table->integer('district_id');
            $table->smallInteger('controller')->default(0);
            $table->float('coal_reserve')->default(0);
            $table->float('required_t')->default(0);
            $table->float('required_p')->default(0);
            $table->string('name');
            $table->string('abbreviation')->nullable();
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
        Schema::dropIfExists('objects');
    }
}
