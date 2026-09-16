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

            // Migrate default SNMP credentials into secrets and set snmp.default_credentials
            $defaultSecretIds = [];
            $snmpVersions = \App\Facades\LibrenmsConfig::get('snmp.version', ['v2c', 'v3', 'v1']);
            $communities = \Illuminate\Support\Arr::wrap(\App\Facades\LibrenmsConfig::get('snmp.community', ['public']));
            $v3Credentials = \App\Facades\LibrenmsConfig::get('snmp.v3', []);

            foreach ($snmpVersions as $version) {
                if ($version === 'v3') {
                    foreach ($v3Credentials as $v3) {
                        $v3Data = [
                            'version' => 'v3',
                            'authlevel' => $v3['authlevel'] ?? 'noAuthNoPriv',
                            'authname' => $v3['authname'] ?? 'root',
                            'authpass' => $v3['authpass'] ?? null,
                            'authalgo' => $v3['authalgo'] ?? 'MD5',
                            'cryptopass' => $v3['cryptopass'] ?? null,
                            'cryptoalgo' => $v3['cryptoalgo'] ?? 'AES',
                        ];
                        $hash = hash('sha256', serialize($v3Data));
                        if (! isset($secretMap[$hash])) {
                            $desc = 'Default SNMP v3 (' . ($v3Data['authname'] ?: 'root') . ')';
                            $existingCount = DB::table('secrets')->where('description', $desc)->count();
                            if ($existingCount > 0) {
                                $desc .= ' #' . ($existingCount + 1);
                            }
                            $secretId = DB::table('secrets')->insertGetId([
                                'description' => $desc,
                                'secret_type' => 'snmp',
                                'data' => encrypt(json_encode($v3Data)),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $secretMap[$hash] = $secretId;
                        }
                        $defaultSecretIds[] = $secretMap[$hash];
                    }
                } elseif (in_array($version, ['v1', 'v2c'])) {
                    foreach ($communities as $community) {
                        if (! is_string($community) || $community === '') {
                            continue;
                        }
                        $v2Data = [
                            'version' => $version,
                            'community' => $community,
                        ];
                        $hash = hash('sha256', serialize($v2Data));
                        if (! isset($secretMap[$hash])) {
                            $desc = "Default SNMP $version ($community)";
                            $existingCount = DB::table('secrets')->where('description', $desc)->count();
                            if ($existingCount > 0) {
                                $desc .= ' #' . ($existingCount + 1);
                            }
                            $secretId = DB::table('secrets')->insertGetId([
                                'description' => $desc,
                                'secret_type' => 'snmp',
                                'data' => encrypt(json_encode($v2Data)),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $secretMap[$hash] = $secretId;
                        }
                        $defaultSecretIds[] = $secretMap[$hash];
                    }
                }
            }

            if (empty($defaultSecretIds)) {
                $v2Data = ['version' => 'v2c', 'community' => 'public'];
                $desc = 'Default SNMP v2c (public)';
                $existingCount = DB::table('secrets')->where('description', $desc)->count();
                if ($existingCount > 0) {
                    $desc .= ' #' . ($existingCount + 1);
                }
                $secretId = DB::table('secrets')->insertGetId([
                    'description' => $desc,
                    'secret_type' => 'snmp',
                    'data' => encrypt(json_encode($v2Data)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $defaultSecretIds[] = $secretId;
            }

            $defaultSecretIds = array_values(array_unique($defaultSecretIds));
            if (! empty($defaultSecretIds)) {
                DB::table('config')->updateOrInsert(
                    ['config_name' => 'snmp.default_credentials'],
                    ['config_value' => json_encode($defaultSecretIds)]
                );
            }
        });
    }
};
