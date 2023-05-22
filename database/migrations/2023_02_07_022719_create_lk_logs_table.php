<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLkLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lk_logs', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id', 255)->change();
            $table->string('status')->nullable();
            $table->string('ket')->nullable();
            $table->string('signature')->nullable();
            $table->text('content');
            $table->timestamps();
            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lk_logs');
    }
}
