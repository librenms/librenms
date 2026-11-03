<?php

namespace LibreNMS\Discovery;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\BulkInsert;
use LibreNMS\Util\ProgressList;

class FdbTable
{
    /**
     * @param  array<int, array<string, array{port_id?: int}>>  $discovered
     * @param  array<int, array<string, array{port_id: int, ports_fdb_id: int}>>  $existing
     * @param  array<int, int>  $vlanNames
     */
    public static function sync(int $deviceId, array $discovered, array $existing, array $vlanNames): void
    {
        if ($discovered === []) {
            return;
        }

        $updateTimeOnly = [];
        $now = Carbon::now();
        foreach ($discovered as $vlanId => $macs) {
            $progress = ProgressList::start((string) ($vlanNames[$vlanId] ?? $vlanId));
            $rows = static function () use ($deviceId, $vlanId, $macs, &$existing, &$updateTimeOnly, $now, $progress) {
                foreach ($macs as $mac => $entry) {
                    if (isset($existing[$vlanId][$mac])) {
                        $previous = $existing[$vlanId][$mac];
                        // An incomplete bridge-port walk must not overwrite a known port with zero.
                        if ($previous['port_id'] != $entry['port_id'] && $entry['port_id'] != 0) {
                            DB::table('ports_fdb')->where('ports_fdb_id', $previous['ports_fdb_id'])->update([
                                'port_id' => $entry['port_id'],
                                'updated_at' => $now,
                            ]);
                            $progress->mark('U');
                        } else {
                            $updateTimeOnly[] = $previous['ports_fdb_id'];
                            $progress->mark('.');
                        }
                        unset($existing[$vlanId][$mac]);
                    } else {
                        yield [
                            'port_id' => $entry['port_id'] ?? 0,
                            'mac_address' => $mac,
                            'vlan_id' => $vlanId,
                            'device_id' => $deviceId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            };

            BulkInsert::insert(DB::table('ports_fdb'), $rows(), fn (int $count) => $progress->mark(str_repeat('+', $count)));
            $progress->end();
        }

        DB::table('ports_fdb')->whereIntegerInRaw('ports_fdb_id', $updateTimeOnly)->update(['updated_at' => $now]);
        // Missing entries are retained until the daily cleanup, as before.
    }
}
