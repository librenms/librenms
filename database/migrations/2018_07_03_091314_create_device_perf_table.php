<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('device_perf', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->index();
            $table->dateTime('timestamp');
            $table->integer('xmt');
            $table->integer('rcv');
            $table->integer('loss');
            $table->float('min');
            $table->float('max');
            $table->float('avg');
            $table->text('debug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('device_perf');
    }
};
