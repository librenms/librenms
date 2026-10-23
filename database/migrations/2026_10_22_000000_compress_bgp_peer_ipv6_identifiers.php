<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Some vendor discovery code stored IPv6 peers uncompressed, others compressed.
     * The bgp-peers module now always stores them compressed, so convert the old rows
     * to keep their ids. Polling renames the matching rrd files.
     */
    public function up(): void
    {
        $this->compress('bgpPeers', ['device_id', 'context_name', 'vrf_id']);
        $this->compress('bgpPeers_cbgp', ['device_id', 'afi', 'safi']);
    }

    public function down(): void
    {
        // compressed addresses are valid for every version
    }

    /**
     * @param  string[]  $scope  columns that identify a row together with the identifier
     */
    private function compress(string $table, array $scope): void
    {
        $rows = DB::table($table)->where('bgpPeerIdentifier', 'like', '%:%')->get();

        foreach ($rows as $row) {
            $packed = @inet_pton($row->bgpPeerIdentifier);
            $compressed = $packed === false ? false : inet_ntop($packed);
            if ($compressed === false || $compressed === $row->bgpPeerIdentifier) {
                continue;
            }

            $peer = DB::table($table);
            foreach ($scope as $column) {
                $peer->where($column, $row->$column);
            }

            $old = (clone $peer)->where('bgpPeerIdentifier', $row->bgpPeerIdentifier);
            if ((clone $peer)->where('bgpPeerIdentifier', $compressed)->exists()) {
                $old->delete(); // already rediscovered under the new identifier
            } else {
                $old->update(['bgpPeerIdentifier' => $compressed]);
            }
        }
    }
};
