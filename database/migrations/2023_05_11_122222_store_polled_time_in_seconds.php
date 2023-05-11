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
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedSmallInteger('last_polled_timetaken')->nullable()->change();
            $table->unsignedSmallInteger('last_discovered_timetaken')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->float('last_polled_timetaken', 5)->nullable()->change();
            $table->float('last_discovered_timetaken', 5)->nullable()->change();
        });
    }
};
