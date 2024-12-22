<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id')->references('id')->on('objects')->onDelete('cascade');
            $table->integer('audit_id')->references('id')->on('audits')->onDelete('cascade');
            $table->integer('auditor_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('audit_json');
            $table->timestamp('audit_date');
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
        Schema::dropIfExists('audit_results');
    }
}
