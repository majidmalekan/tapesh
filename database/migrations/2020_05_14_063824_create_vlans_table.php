<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vlans', function (Blueprint $table) {
            $table->increments('vlan_id');
            $table->string('vlanName')->unique();
            $table->string('id')->unique();
            $table->string('mac-address')->unique();
            $table->string('rx-byte');
            $table->string('tx-byte');
            $table->string('disabled');
            $table->string('running');
            $table->string('last-link-up-time');
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
        Schema::dropIfExists('vlans');
    }
}
