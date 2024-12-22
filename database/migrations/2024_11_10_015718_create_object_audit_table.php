<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_audit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id')->references('id')->on('objects')->onDelete('cascade');
            $table->integer('audit_id')->references('id')->on('audits')->onDelete('cascade');
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
        Schema::dropIfExists('object_audit');
    }
}
