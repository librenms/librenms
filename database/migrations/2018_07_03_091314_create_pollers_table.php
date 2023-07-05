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
        Schema::create('pollers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('poller_name')->unique();
            $table->dateTime('last_polled');
            $table->unsignedInteger('devices');
            $table->float('time_taken', 10, 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('pollers');
    }
};
