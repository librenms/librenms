<?php

use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    private const PORT_ASSOCIATION_MODES = [1 => 'ifIndex', 2 => 'ifName', 3 => 'ifDescr', 4 => 'ifAlias'];

    /** @var array<string, int> */
    private array $secretMap = [];

    /** @var array<int, array{count: int, hostname: string, version: string}> */
    private array $secretMeta = [];

    /** @var array<string, bool> */
    private array $usedDescriptions = [];

    public function up(): void
    {
        // Legacy columns always hold a value, only keep the ones that differ from the global default
        $defaults = [
            'port' => (int) \App\Facades\LibrenmsConfig::get('snmp.port', 161),
            'transport' => \App\Facades\LibrenmsConfig::get('snmp.transports.0', 'udp'),
            'port_association_mode' => \App\Facades\LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
        ];

        // Small chunks, each in a short transaction, to avoid holding locks for long
        DB::table('devices')
            ->select(['device_id', 'hostname', 'status', 'snmp_disable', 'snmpver', 'community', 'authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo', 'port', 'transport', 'timeout', 'retries', 'port_association_mode'])
            ->chunkById(100, function ($devices) use ($defaults) {
                DB::transaction(function () use ($devices, $defaults) {
                    $deviceIds = $devices->pluck('device_id')->all();

                    // Skip devices that already have an SNMP polling method
                    $existingDeviceIds = DB::table('device_polling_methods')
                        ->where('method_type', 'snmp')
                        ->whereIn('device_id', $deviceIds)
                        ->pluck('device_id')
                        ->all();

                    $deviceIds = array_diff($deviceIds, $existingDeviceIds);

                    if (empty($deviceIds)) {
                        return;
                    }

                    $attribsByDevice = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->whereIn('attrib_type', ['snmp_max_repeaters', 'snmp_max_oid'])
                        ->get()
                        ->groupBy('device_id');

                    $pollingMethods = [];

                    foreach ($devices as $device) {
                        if (in_array($device->device_id, $existingDeviceIds)) {
                            continue;
                        }

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

                        $secretId = $this->getOrCreateSecret($data, "SNMP for device $device->hostname");

                        if ($secretId !== null) {
                            if (! isset($this->secretMeta[$secretId])) {
                                $this->secretMeta[$secretId] = [
                                    'count' => 1,
                                    'hostname' => $device->hostname,
                                    'version' => $snmpver,
                                ];
                            } else {
                                $this->secretMeta[$secretId]['count']++;
                            }
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
                            'port_association_mode' => self::PORT_ASSOCIATION_MODES[$device->port_association_mode] ?? null,
                        ], fn ($v, $k) => $v !== null && $v !== ($defaults[$k] ?? null), ARRAY_FILTER_USE_BOTH);

                        $pollingMethods[] = [
                            'device_id' => $device->device_id,
                            'method_type' => 'snmp',
                            'enabled' => ! $device->snmp_disable,
                            'affects_availability' => true,
                            'last_check_successful' => (bool) $device->status,
                            'secret_id' => $secretId,
                            'settings' => json_encode($settings),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (! empty($pollingMethods)) {
                        DB::table('device_polling_methods')->insert($pollingMethods);
                    }
                });
            }, 'device_id');

        // Update descriptions for shared secrets
        $id = 0;
        foreach ($this->secretMeta as $secretId => $meta) {
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
                    $secretId = $this->getOrCreateSecret($v3Data, 'Default SNMP v3 (' . ($v3Data['authname'] ?: 'root') . ')');
                    if ($secretId !== null) {
                        $defaultSecretIds[] = $secretId;
                    }
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
                    $secretId = $this->getOrCreateSecret($v2Data, "Default SNMP $version ($community)");
                    if ($secretId !== null) {
                        $defaultSecretIds[] = $secretId;
                    }
                }
            }
        }

        if (empty($defaultSecretIds)) {
            $v2Data = ['version' => 'v2c', 'community' => 'public'];
            $secretId = $this->getOrCreateSecret($v2Data, 'Default SNMP v2c (public)');
            if ($secretId !== null) {
                $defaultSecretIds[] = $secretId;
            }
        }

        $defaultSecretIds = array_values(array_unique($defaultSecretIds));
        if (! empty($defaultSecretIds)) {
            DB::table('config')->updateOrInsert(
                ['config_name' => 'snmp.default_credentials'],
                ['config_value' => json_encode($defaultSecretIds)]
            );
        }
    }

    public function down(): void
    {
        DB::table('device_polling_methods')->where('method_type', 'snmp')->delete();
        DB::table('secrets')->where('secret_type', 'snmp')->delete();
        DB::table('config')->where('config_name', 'snmp.default_credentials')->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function getOrCreateSecret(array $data, string $desc, string $type = 'snmp'): ?int
    {
        $hash = hash('sha256', serialize($data));
        if (isset($this->secretMap[$hash])) {
            return $this->secretMap[$hash];
        }

        $originalDesc = $desc;
        $counter = 1;
        while (isset($this->usedDescriptions[$desc]) || DB::table('secrets')->where('description', $desc)->exists()) {
            $counter++;
            $desc = "$originalDesc #$counter";
        }
        $this->usedDescriptions[$desc] = true;

        try {
            $secretId = DB::table('secrets')->insertGetId([
                'description' => $desc,
                'secret_type' => $type,
                'data' => encrypt(json_encode($data)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->secretMap[$hash] = $secretId;

            return $secretId;
        } catch (EncryptException $e) {
            Log::warning("Failed to encrypt secret ($desc): {$e->getMessage()}");

            return null;
        }
    }
};
