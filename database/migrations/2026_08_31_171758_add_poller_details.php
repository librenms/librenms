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
        Schema::table('pollers', function (Blueprint $table) {
            $table->longText('poller_details')->nullable()->after('time_taken');
        });

        Schema::table('poller_cluster', function (Blueprint $table) {
            $table->longText('poller_details')->nullable()->after('poller_version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pollers', function (Blueprint $table) {
            $table->dropColumn('poller_details');
        });

        Schema::table('poller_cluster', function (Blueprint $table) {
            $table->dropColumn('poller_details');
        });
    }
};
