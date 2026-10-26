<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // contexts mapped to a VRF become the context of that VRF, they were walked until now
        foreach (DB::table('vrf_lite_cisco')->get() as $vrfLite) {
            $name = (string) $vrfLite->vrf_name;
            if ($name === '' || strcasecmp($name, 'default') == 0) {
                continue; // not a VRF (bridge domain contexts) or the default VRF
            }

            $updated = DB::table('vrfs')
                ->where('device_id', $vrfLite->device_id)
                ->where('vrf_name', $name)
                ->whereNull('context_name')
                ->update(['context_name' => $vrfLite->context_name, 'context_verified' => true]);

            if (! $updated && ! DB::table('vrfs')->where('device_id', $vrfLite->device_id)->where('vrf_name', $name)->exists()) {
                DB::table('vrfs')->insert([
                    'device_id' => $vrfLite->device_id,
                    'vrf_oid' => $name,
                    'vrf_name' => $name,
                    'context_name' => $vrfLite->context_name,
                    'context_verified' => true,
                    'mplsVpnVrfDescription' => '',
                ]);
            }
        }

        // vrf_oid is the VRF index used in the vendor MIB, without prefixes
        foreach (DB::table('vrfs')->where('vrf_oid', 'like', 'firebrick.%')->get(['vrf_id', 'vrf_oid']) as $vrf) {
            DB::table('vrfs')->where('vrf_id', $vrf->vrf_id)->update(['vrf_oid' => substr((string) $vrf->vrf_oid, 10)]);
        }

        // ARISTA-VRF-MIB is indexed by the length prefixed VRF name
        $aristaVrfs = DB::table('vrfs')
            ->join('devices', 'devices.device_id', '=', 'vrfs.device_id')
            ->whereIn('devices.os', ['arista_eos', 'arista-mos'])
            ->get(['vrfs.vrf_id', 'vrfs.vrf_oid']);
        foreach ($aristaVrfs as $vrf) {
            $oid = (string) $vrf->vrf_oid;
            if (! preg_match('/^\d+(\.\d+)*$/', $oid)) {
                $encoded = implode('.', [strlen($oid), ...array_map(ord(...), str_split($oid))]);
                DB::table('vrfs')->where('vrf_id', $vrf->vrf_id)->update(['vrf_oid' => $encoded]);
            }
        }

        Schema::drop('vrf_lite_cisco');

        // manual contexts were a device setting, they are now stored on the VRF they expose
        foreach (DB::table('devices_attribs')->where('attrib_type', 'routing_snmp_contexts')->get() as $attrib) {
            $contexts = json_decode((string) $attrib->attrib_value, true);
            foreach (is_array($contexts) ? $contexts : [] as $context) {
                if (! is_string($context) || $context === '' || strcasecmp($context, 'default') == 0) {
                    continue;
                }

                $findVrf = fn (string $column) => DB::table('vrfs')->where('device_id', $attrib->device_id)->where($column, $context)->first();

                if ($findVrf('context_name') !== null) {
                    continue; // the device maps it
                }

                $named = $findVrf('vrf_name');
                if ($named !== null) {
                    if ($named->context_name === null) {
                        DB::table('vrfs')->where('vrf_id', $named->vrf_id)->update(['context_name' => $context, 'context_manual' => true]);
                    }

                    continue;
                }

                // VRFs only known through their context have a prefixed vrf_oid, so it never matches a MIB index
                DB::table('vrfs')->insert([
                    'device_id' => $attrib->device_id,
                    'vrf_oid' => 'context:' . $context,
                    'vrf_name' => $context,
                    'context_name' => $context,
                    'context_manual' => true,
                    'vrf_manual' => true,
                    'mplsVpnVrfDescription' => '',
                ]);
            }
        }

        DB::table('devices_attribs')->where('attrib_type', 'routing_snmp_contexts')->delete();
    }

    public function down(): void
    {
        foreach (DB::table('vrfs')->where('context_manual', true)->get()->groupBy('device_id') as $deviceId => $vrfs) {
            DB::table('devices_attribs')->insert([
                'device_id' => $deviceId,
                'attrib_type' => 'routing_snmp_contexts',
                'attrib_value' => json_encode($vrfs->pluck('context_name')->values()->all()),
            ]);
        }
        DB::table('vrfs')->where('vrf_manual', true)->delete();

        Schema::create('vrf_lite_cisco', function (Blueprint $table): void {
            $table->increments('vrf_lite_cisco_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('context_name', 128)->index();
            $table->string('intance_name', 128)->nullable()->default('');
            $table->string('vrf_name', 128)->nullable()->default('Default')->index();
            $table->index(['device_id', 'context_name', 'vrf_name']);
        });

        foreach (DB::table('vrfs')->whereNotNull('context_name')->where('context_manual', false)->get() as $vrf) {
            DB::table('vrf_lite_cisco')->insert([
                'device_id' => $vrf->device_id,
                'context_name' => $vrf->context_name,
                'vrf_name' => $vrf->vrf_name,
            ]);
        }
    }
};
