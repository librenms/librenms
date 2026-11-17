<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports_fdb', function (Blueprint $table): void {
            $table->unsignedInteger('vlan')->nullable()->after('mac_address')->index();
        });

        // Timos and Edgeswitch could store the VLAN number as vlan_id, which may match an unrelated vlans row.
        // Those stay null and discovery finds them again.
        DB::table('ports_fdb')
            ->whereNotIn('device_id', DB::table('devices')->select('device_id')->whereIn('os', ['edgeswitch', 'timos']))
            ->update(['vlan' => DB::raw(
                'NULLIF((SELECT vlan_vlan FROM vlans WHERE vlans.vlan_id = ports_fdb.vlan_id AND vlans.device_id = ports_fdb.device_id), 0)'
            )]);

        Schema::table('ports_fdb', function (Blueprint $table): void {
            $table->dropIndex('ports_fdb_vlan_id_index');
            $table->dropColumn('vlan_id');
        });
    }

    public function down(): void
    {
        Schema::table('ports_fdb', function (Blueprint $table): void {
            $table->unsignedInteger('vlan_id')->default(0)->after('mac_address')->index();
        });

        DB::table('ports_fdb')->update(['vlan_id' => DB::raw(
            'COALESCE((SELECT vlan_id FROM vlans WHERE vlans.vlan_vlan = ports_fdb.vlan AND vlans.device_id = ports_fdb.device_id LIMIT 1), 0)'
        )]);

        Schema::table('ports_fdb', function (Blueprint $table): void {
            $table->dropIndex('ports_fdb_vlan_index');
            $table->dropColumn('vlan');
        });
    }
};
