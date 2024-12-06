<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('device_perf');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
            $table->index(['device_id', 'timestamp']);
        });
    }
};
