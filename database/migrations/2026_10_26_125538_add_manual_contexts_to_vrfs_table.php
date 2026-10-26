<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vrfs', function (Blueprint $table): void {
            $table->boolean('context_manual')->default(false)->after('context_name');
            $table->boolean('vrf_manual')->default(false)->after('context_manual');
        });

        // manual contexts were a device setting, they are now stored on the VRF they expose
        foreach (DB::table('devices_attribs')->where('attrib_type', 'routing_snmp_contexts')->get() as $attrib) {
            $contexts = json_decode((string) $attrib->attrib_value, true);
            foreach (is_array($contexts) ? $contexts : [] as $context) {
                if (! is_string($context) || $context === '' || strcasecmp($context, 'default') == 0) {
                    continue;
                }

                $findVrf = fn (string $column) => DB::table('vrfs')->where('device_id', $attrib->device_id)->where($column, $context)->first();

                $existing = $findVrf('context_name');
                if ($existing !== null) {
                    if ($existing->vrf_oid === $context && $existing->vrf_name === $context) {
                        // created from the manual context by discovery
                        DB::table('vrfs')->where('vrf_id', $existing->vrf_id)->update(['context_manual' => true, 'vrf_manual' => true]);
                    }

                    continue;
                }

                $named = $findVrf('vrf_name');
                if ($named !== null) {
                    if ($named->context_name === null) {
                        DB::table('vrfs')->where('vrf_id', $named->vrf_id)->update(['context_name' => $context, 'context_manual' => true]);
                    }

                    continue;
                }

                if ($findVrf('vrf_oid') === null) {
                    DB::table('vrfs')->insert([
                        'device_id' => $attrib->device_id,
                        'vrf_oid' => $context,
                        'vrf_name' => $context,
                        'context_name' => $context,
                        'context_manual' => true,
                        'vrf_manual' => true,
                        'mplsVpnVrfDescription' => '',
                    ]);
                }
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

        Schema::table('vrfs', function (Blueprint $table): void {
            $table->dropColumn(['context_manual', 'vrf_manual']);
        });
    }
};
