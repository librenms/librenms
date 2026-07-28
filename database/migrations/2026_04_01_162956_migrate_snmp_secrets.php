<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\PortAssociationMode;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $secretMap = []; // hash => secret_id
            $secretMeta = []; // secret_id => ['count' => int, 'hostname' => string]

            DB::table('devices')
                ->orderBy('device_id')
                ->chunk(100, function ($devices) use (&$secretMap, &$secretMeta) {
                    $deviceIds = $devices->pluck('device_id')->all();

                    $attribsByDevice = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->whereIn('attrib_type', ['snmp_max_repeaters', 'snmp_max_oid'])
                        ->get()
                        ->groupBy('device_id');

                    $pollingMethods = [];

                    foreach ($devices as $device) {
                        $snmpver = $device->snmpver ?? 'v2c';
                        $data = ['version' => $snmpver];

                        if ($snmpver === 'v3') {
                            $data['authlevel'] = $device->authlevel ?? 'noAuthNoPriv';
                            $data['authname'] = $device->authname ?? '';
                            $data['authpass'] = $device->authpass ?? null;
                            $data['authalgo'] = $device->authalgo ?? 'MD5';
                            $data['cryptopass'] = $device->cryptopass ?? null;
                            $data['cryptoalgo'] = $device->cryptoalgo ?? 'AES';
                        } else {
                            $data['community'] = $device->community ?? 'public';
                        }

                        $hash = hash('sha256', serialize($data));

                        if (! isset($secretMap[$hash])) {
                            $secretId = DB::table('secrets')->insertGetId([
                                'description' => "SNMP for device $device->hostname",
                                'secret_type' => 'snmp',
                                'default' => false,
                                'data' => encrypt(json_encode($data)),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $secretMap[$hash] = $secretId;
                            $secretMeta[$secretId] = [
                                'count' => 1,
                                'hostname' => $device->hostname,
                                'version' => $snmpver,
                            ];
                        } else {
                            $secretId = $secretMap[$hash];
                            $secretMeta[$secretId]['count']++;
                        }

                        $attribs = ($attribsByDevice[$device->device_id] ?? collect())
                            ->pluck('attrib_value', 'attrib_type');

                        $settings = array_filter([
                            'port' => $device->port !== null ? (int) $device->port : null,
                            'transport' => $device->transport,
                            'timeout' => $device->timeout !== null ? (int) $device->timeout : null,
                            'retries' => $device->retries !== null ? (int) $device->retries : null,
                            'max_repeaters' => isset($attribs['snmp_max_repeaters']) ? (int) $attribs['snmp_max_repeaters'] : null,
                            'max_oid' => isset($attribs['snmp_max_oid']) ? (int) $attribs['snmp_max_oid'] : null,
                            'port_association_mode' => isset($device->port_association_mode) ? PortAssociationMode::getName((int) $device->port_association_mode) : null,
                        ], fn ($v) => $v !== null);

                        $pollingMethods[] = [
                            'device_id' => $device->device_id,
                            'method_type' => 'snmp',
                            'enabled' => ! $device->snmp_disable,
                            'affects_availability' => true,
                            'secret_id' => $secretId,
                            'settings' => json_encode($settings),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    DB::table('device_polling_methods')->insert($pollingMethods);
                });

            // Update descriptions for shared secrets
            $id = 0;
            foreach ($secretMeta as $secretId => $meta) {
                if ($meta['count'] > 1) {
                    $id++;
                    DB::table('secrets')->where('id', $secretId)->update([
                        'description' => "SNMP {$meta['version']} (shared #$id)",
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
};
