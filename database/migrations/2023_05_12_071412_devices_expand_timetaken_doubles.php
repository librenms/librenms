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
        Schema::table('devices', function (Blueprint $table) {
            $table->float('last_polled_timetaken')->unsigned()->nullable()->change();
            $table->float('last_discovered_timetaken')->unsigned()->nullable()->change();
            $table->float('last_ping_timetaken')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // can't undo the places change
        Schema::table('devices', function (Blueprint $table) {
            $table->float('last_polled_timetaken')->nullable()->change();
            $table->float('last_discovered_timetaken')->nullable()->change();
            $table->float('last_ping_timetaken')->nullable()->change();
        });
    }
};
