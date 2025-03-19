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
            $table->unsignedFloat('last_polled_timetaken')->nullable()->change();
            $table->unsignedFloat('last_discovered_timetaken')->nullable()->change();
            $table->unsignedFloat('last_ping_timetaken')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // can't undo the places change
        Schema::table('devices', function (Blueprint $table) {
            $table->float('last_polled_timetaken')->nullable()->change();
            $table->float('last_discovered_timetaken')->nullable()->change();
            $table->float('last_ping_timetaken')->nullable()->change();
        });
    }
};
