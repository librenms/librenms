<?php

use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /** @var array<string, int> */
    private array $secretMap = [];

    /** @var array<int, array{count: int, hostname: string}> */
    private array $secretMeta = [];

    /** @var array<string, bool> */
    private array $usedDescriptions = [];

    public function up(): void
    {
        DB::table('devices')
            ->orderBy('devices.device_id')
            ->chunk(100, function ($devices) {
                DB::transaction(function () use ($devices) {
                    $deviceIds = $devices->pluck('device_id')->all();

                    $attribsByDevice = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->whereIn('attrib_type', ['ipmi_hostname', 'ipmi_port', 'ipmi_ciphersuite', 'ipmi_timeout', 'ipmi_username', 'ipmi_password', 'ipmi_kg_key'])
                        ->get()
                        ->groupBy('device_id');

                    $hostnamesByDevice = $devices->pluck('hostname', 'device_id');

                    $pollingMethods = [];

                    foreach ($deviceIds as $deviceId) {
                        $attribs = ($attribsByDevice[$deviceId] ?? collect())
                            ->pluck('attrib_value', 'attrib_type');

                        if (empty($attribs['ipmi_hostname'])) {
                            continue;
                        }

                        $data = [
                            'username' => $attribs['ipmi_username'] ?? '',
                            'password' => $attribs['ipmi_password'] ?? '',
                            'kg_key' => $attribs['ipmi_kg_key'] ?? null,
                        ];

                        $secretId = $this->getOrCreateSecret($data, "IPMI for device {$hostnamesByDevice[$deviceId]}", 'ipmi');

                        if ($secretId !== null) {
                            if (! isset($this->secretMeta[$secretId])) {
                                $this->secretMeta[$secretId] = [
                                    'count' => 1,
                                    'hostname' => $hostnamesByDevice[$deviceId],
                                ];
                            } else {
                                $this->secretMeta[$secretId]['count']++;
                            }
                        }

                        $settings = array_filter([
                            'hostname' => $attribs['ipmi_hostname'] ?? null,
                            'port' => isset($attribs['ipmi_port']) ? (int) $attribs['ipmi_port'] : 623,
                            'ciphersuite' => $attribs['ipmi_ciphersuite'] ?? '',
                            'timeout' => isset($attribs['ipmi_timeout']) ? (int) $attribs['ipmi_timeout'] : 3,
                        ], fn ($v) => $v !== null);

                        $pollingMethods[] = [
                            'device_id' => $deviceId,
                            'method_type' => 'ipmi',
                            'enabled' => true,
                            'affects_availability' => false,
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
            });

        $id = 0;
        foreach ($this->secretMeta as $secretId => $meta) {
            if ($meta['count'] > 1) {
                $id++;
                DB::table('secrets')->where('id', $secretId)->update([
                    'description' => "IPMI (shared #$id)",
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function getOrCreateSecret(array $data, string $desc, string $type = 'ipmi'): ?int
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
