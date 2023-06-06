<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->string('tiket')->nullable();
            $table->bigInteger('deposit')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('member');
    }
};
