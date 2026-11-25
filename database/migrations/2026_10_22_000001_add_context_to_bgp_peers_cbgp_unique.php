<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The same peer address may exist in several SNMP contexts (VRFs)
     */
    public function up(): void
    {
        Schema::table('bgpPeers_cbgp', function (Blueprint $table): void {
            $table->dropUnique('bgppeers_cbgp_device_id_bgppeeridentifier_afi_safi_unique');
            $table->dropIndex('bgppeers_cbgp_device_id_bgppeeridentifier_context_name_index');
            $table->unique(['device_id', 'bgpPeerIdentifier', 'context_name', 'afi', 'safi'], 'bgppeers_cbgp_peer_family_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bgpPeers_cbgp', function (Blueprint $table): void {
            $table->dropUnique('bgppeers_cbgp_peer_family_unique');
            $table->index(['device_id', 'bgpPeerIdentifier', 'context_name']);
            $table->unique(['device_id', 'bgpPeerIdentifier', 'afi', 'safi']);
        });
    }
};
