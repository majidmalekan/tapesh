<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVpnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vpns', function (Blueprint $table) {
            $table->increments('vpn_id');
            $table->string('customer');
            $table->string('id')->unique();
            $table->string('actual-profile');
            $table->string('username');
            $table->string('password');
            $table->string('disabled');
            $table->string('uptime-used')->nullable();
            $table->string('download-used')->nullable();
            $table->string('upload-used')->nullable();
            $table->string('active');
            $table->string('incomplete');
            $table->string('last-seen');
            $table->string('shared-users');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('user_id')->on('users');
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
        Schema::dropIfExists('vpns');
    }
}
