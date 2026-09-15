<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropNullDuplicates('bgpPeers', ['device_id', 'bgpPeerIdentifier']);
        $this->dropNullDuplicates('bgpPeers_cbgp', ['device_id', 'bgpPeerIdentifier', 'afi', 'safi']);

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
     * where an empty string row already exists. Polling updated both, so they only
     * differ by id, and the empty string row is the one discovery matches from now on.
     *
     * @param  list<string>  $keyColumns
     */
    private function dropNullDuplicates(string $table, array $keyColumns): void
    {
        $duplicates = DB::table($table)
            ->select($keyColumns)
            ->whereNull('context_name')
            ->groupBy($keyColumns)
            ->get();

        foreach ($duplicates as $duplicate) {
            $hasEmptyContext = DB::table($table)->where('context_name', '');

            foreach ($keyColumns as $column) {
                $hasEmptyContext->where($column, $duplicate->$column);
            }

            if (! $hasEmptyContext->exists()) {
                continue;
            }

            $nullRows = DB::table($table)->whereNull('context_name');

            foreach ($keyColumns as $column) {
                $nullRows->where($column, $duplicate->$column);
            }

            $nullRows->delete();
        }
    }
};
