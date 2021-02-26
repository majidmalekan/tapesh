<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('username')->unique();
            $table->string('api_token')->unique();
            $table->integer('role_id')->unsigned();
            $table->string('phone')->unique()->nullable();
            $table->integer('viaPhone')->default('0');
            $table->integer('viaEmail')->default('0');
            $table->string('companyName')->nullable();
            $table->string('lastLogin');
            $table->string('createIran');
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
        Schema::dropIfExists('users');
    }
}
