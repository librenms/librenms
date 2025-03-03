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
    public function up(): void
    {
        Schema::drop('perf_times');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::create('perf_times', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 8)->index();
            $table->string('doing', 64);
            $table->unsignedInteger('start');
            $table->float('duration');
            $table->unsignedInteger('devices');
            $table->string('poller');
        });
    }
};
