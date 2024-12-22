<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditAssignedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_assigned', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('device_id')->references('id')->on('objects')->onDelete('cascade');
            $table->integer('audit_id')->references('id')->on('audits')->onDelete('cascade');
            $table->integer('month');
            $table->integer('audits_count');
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
        Schema::dropIfExists('audit_assigned');
    }
}
