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
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->boolean('override_bandwidth')->default(0)->after('port_id');
            $table->bigInteger('rx_bandwidth')->nullable()->after('override_bandwidth');
            $table->bigInteger('tx_bandwidth')->nullable()->after('rx_bandwidth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->dropColumn(['override_bandwidth', 'rx_bandwidth', 'tx_bandwidth']);
        });
    }
};