<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('plan_id');
            $table->string('trunk');
            $table->string('speed');
            $table->string('period');
            $table->string('price');
            $table->string('priceUnit');
            $table->string('code')->unique();
            $table->string('trunkUnit');
            $table->string('speedUnit');
            $table->string('createIran');
            $table->string('lastUpdate');
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
        Schema::dropIfExists('plans');
    }
}
