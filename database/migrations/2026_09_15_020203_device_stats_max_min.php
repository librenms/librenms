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
        Schema::table('device_stats', function (Blueprint $table) {
            $table->float('ping_rttmax_last')->unsigned()->nullable()->after('ping_rtt_last');
            $table->float('ping_rttmin_last')->unsigned()->nullable()->after('ping_rttmax_last');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_stats', function (Blueprint $table) {
            $table->dropColumn('ping_rrtmax_last');
            $table->dropColumn('ping_rrtmin_last');
        });
    }
};
