<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds tunnel_index to support multiple IPsec tunnels per peer (e.g. Juniper SRX Phase 2).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->unsignedInteger('tunnel_index')->default(0)->after('device_id');
        });

        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->dropUnique(['device_id', 'peer_addr']);
            $table->unique(['device_id', 'peer_addr', 'tunnel_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->dropUnique(['device_id', 'peer_addr', 'tunnel_index']);
            $table->unique(['device_id', 'peer_addr']);
        });

        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->dropColumn('tunnel_index');
        });
    }
};
