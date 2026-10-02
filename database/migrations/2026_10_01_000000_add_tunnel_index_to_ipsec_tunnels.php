<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // rows that only differ by tunnel_index would violate the restored (device_id, peer_addr) unique index
        DB::table('ipsec_tunnels')->where('tunnel_index', '!=', 0)->delete();

        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->dropUnique(['device_id', 'peer_addr', 'tunnel_index']);
            $table->unique(['device_id', 'peer_addr']);
        });

        Schema::table('ipsec_tunnels', function (Blueprint $table) {
            $table->dropColumn('tunnel_index');
        });
    }
};
