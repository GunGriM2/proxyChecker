<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxyResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_results', function (Blueprint $table) {
            $table->id();
            $table->string('proxy');
            $table->boolean('status');
            $table->string('type')->nullable();
            $table->string('city')->nullable();
            $table->string('speed')->nullable();
            $table->foreignId('proxy_check_id')->references('id')->on('proxy_checks');
            $table->boolean('completed');
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
        Schema::dropIfExists('proxy_results');
    }
}
