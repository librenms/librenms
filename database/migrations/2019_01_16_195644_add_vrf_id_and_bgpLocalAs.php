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
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->unsignedInteger('vrf_id')->nullable()->after('device_id');
        });
        Schema::table('vrfs', function (Blueprint $table) {
            $table->unsignedInteger('bgpLocalAs')->nullable()->after('vrf_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->dropColumn('vrf_id');
        });
        Schema::table('vrfs', function (Blueprint $table) {
            $table->dropColumn('bgpLocalAs');
        });
    }
};
