<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropNullDuplicates();

        DB::table('bgpPeers')->whereNull('context_name')->update(['context_name' => '']);
        DB::table('bgpPeers_cbgp')->whereNull('context_name')->update(['context_name' => '']);

        Schema::table('bgpPeers', function (Blueprint $table): void {
            $table->string('context_name', 128)->default('')->nullable(false)->change();
        });

        Schema::table('bgpPeers_cbgp', function (Blueprint $table): void {
            $table->string('context_name', 128)->default('')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bgpPeers', function (Blueprint $table): void {
            $table->string('context_name', 128)->nullable()->default(null)->change();
        });

        Schema::table('bgpPeers_cbgp', function (Blueprint $table): void {
            $table->string('context_name', 128)->nullable()->default(null)->change();
        });
    }

    /**
     * The os specific discovery modules stored a null context while the generic module
     * looked for an empty string, so each peer ended up with two rows. Drop the null row
     * where an empty string row already exists. Polling updated both, so they only differ
     * by id, and the empty string row is the one discovery matches from now on.
     *
     * bgpPeers_cbgp needs no such pass. Its device_id, bgpPeerIdentifier, afi and safi
     * unique index already prevented a second row, so only the context differs there.
     */
    private function dropNullDuplicates(): void
    {
        $duplicates = DB::table('bgpPeers')
            ->select(['device_id', 'bgpPeerIdentifier'])
            ->whereNull('context_name')
            ->groupBy(['device_id', 'bgpPeerIdentifier'])
            ->get();

        foreach ($duplicates as $duplicate) {
            $hasEmptyContext = DB::table('bgpPeers')
                ->where('context_name', '')
                ->where('device_id', $duplicate->device_id)
                ->where('bgpPeerIdentifier', $duplicate->bgpPeerIdentifier)
                ->exists();

            if (! $hasEmptyContext) {
                continue;
            }

            DB::table('bgpPeers')
                ->whereNull('context_name')
                ->where('device_id', $duplicate->device_id)
                ->where('bgpPeerIdentifier', $duplicate->bgpPeerIdentifier)
                ->delete();
        }
    }
};
