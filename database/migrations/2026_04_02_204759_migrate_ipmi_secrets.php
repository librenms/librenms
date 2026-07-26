<?php

use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $secretMap = [];
            $secretMeta = [];

            DB::table('devices')
                ->orderBy('devices.device_id')
                ->chunk(100, function ($devices) use (&$secretMap, &$secretMeta) {
                    $deviceIds = $devices->pluck('device_id')->all();

                    $attribsByDevice = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->whereIn('attrib_type', ['ipmi_hostname', 'ipmi_username', 'ipmi_password', 'ipmi_kg_key'])
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

                        try {
                            $hash = hash('sha256', serialize($data));

                            if (! isset($secretMap[$hash])) {
                                $secretId = DB::table('secrets')->insertGetId([
                                    'description' => "IPMI for device {$hostnamesByDevice[$deviceId]}",
                                    'secret_type' => 'ipmi',
                                    'default' => false,
                                    'data' => encrypt(json_encode($data)),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $secretMap[$hash] = $secretId;
                                $secretMeta[$secretId] = [
                                    'count' => 1,
                                    'hostname' => $hostnamesByDevice[$deviceId],
                                ];
                            } else {
                                $secretId = $secretMap[$hash];
                                $secretMeta[$secretId]['count']++;
                            }

                            $pollingMethods[] = [
                                'device_id' => $deviceId,
                                'method_type' => 'ipmi',
                                'enabled' => true,
                                'affects_availability' => false,
                                'secret_id' => $secretId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } catch (EncryptException) {
                            // ignore
                        }
                    }

                    if (! empty($pollingMethods)) {
                        DB::table('device_polling_methods')->insert($pollingMethods);
                    }
                });

            $id = 0;
            foreach ($secretMeta as $secretId => $meta) {
                if ($meta['count'] > 1) {
                    $id++;
                    DB::table('secrets')->where('id', $secretId)->update([
                        'description' => "IPMI (shared #$id)",
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
};
