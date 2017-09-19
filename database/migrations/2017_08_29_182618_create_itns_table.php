<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('status_code');
            $table->string('email');
            $table->string('order_id');
            $table->string('order_ref');
            $table->text('xml');
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
        Schema::dropIfExists('itns');
    }
}
