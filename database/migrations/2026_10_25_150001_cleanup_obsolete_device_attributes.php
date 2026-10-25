<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('devices_attribs')
            ->whereIn('attrib_type', [
                'override_icmp_disable',
                'ipmi_hostname',
                'ipmi_port',
                'ipmi_ciphersuite',
                'ipmi_timeout',
                'ipmi_username',
                'ipmi_password',
                'ipmi_kg_key',
                'snmp_max_repeaters',
                'snmp_max_oid',
            ])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-populate override_icmp_disable for disabled icmp
        DB::table('device_polling_methods')
            ->where('method_type', 'icmp')
            ->where('enabled', false)
            ->orderBy('id')
            ->chunk(100, function ($pollingMethods): void {
                $attribs = [];
                foreach ($pollingMethods as $method) {
                    $attribs[] = [
                        'device_id' => $method->device_id,
                        'attrib_type' => 'override_icmp_disable',
                        'attrib_value' => 'true',
                    ];
                }
                if (! empty($attribs)) {
                    DB::table('devices_attribs')->insert($attribs);
                }
            });

        // Re-populate snmp_max_repeaters and snmp_max_oid from SNMP polling methods
        DB::table('device_polling_methods')
            ->where('method_type', 'snmp')
            ->orderBy('id')
            ->chunk(100, function ($pollingMethods): void {
                $attribs = [];
                foreach ($pollingMethods as $method) {
                    $settings = json_decode($method->settings, true) ?: [];
                    if (isset($settings['max_repeaters'])) {
                        $attribs[] = [
                            'device_id' => $method->device_id,
                            'attrib_type' => 'snmp_max_repeaters',
                            'attrib_value' => (string) $settings['max_repeaters'],
                        ];
                    }
                    if (isset($settings['max_oid'])) {
                        $attribs[] = [
                            'device_id' => $method->device_id,
                            'attrib_type' => 'snmp_max_oid',
                            'attrib_value' => (string) $settings['max_oid'],
                        ];
                    }
                }
                if (! empty($attribs)) {
                    DB::table('devices_attribs')->insert($attribs);
                }
            });

        // Re-populate IPMI attributes from IPMI polling methods and secrets
        DB::table('device_polling_methods')
            ->where('method_type', 'ipmi')
            ->orderBy('id')
            ->chunk(100, function ($pollingMethods): void {
                $attribs = [];
                $secretIds = array_filter(array_column($pollingMethods->all(), 'secret_id'));
                $secrets = ! empty($secretIds)
                    ? DB::table('secrets')->whereIn('id', $secretIds)->get()->keyBy('id')
                    : collect();

                foreach ($pollingMethods as $method) {
                    $settings = json_decode($method->settings, true) ?: [];
                    if (! empty($settings['hostname'])) {
                        $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_hostname', 'attrib_value' => (string) $settings['hostname']];
                    }
                    if (isset($settings['port'])) {
                        $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_port', 'attrib_value' => (string) $settings['port']];
                    }
                    if (isset($settings['ciphersuite'])) {
                        $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_ciphersuite', 'attrib_value' => (string) $settings['ciphersuite']];
                    }
                    if (isset($settings['timeout'])) {
                        $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_timeout', 'attrib_value' => (string) $settings['timeout']];
                    }

                    if ($method->secret_id && isset($secrets[$method->secret_id])) {
                        try {
                            $secretData = json_decode(decrypt($secrets[$method->secret_id]->data), true) ?: [];
                            if (! empty($secretData['username'])) {
                                $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_username', 'attrib_value' => (string) $secretData['username']];
                            }
                            if (! empty($secretData['password'])) {
                                $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_password', 'attrib_value' => (string) $secretData['password']];
                            }
                            if (! empty($secretData['kg_key'])) {
                                $attribs[] = ['device_id' => $method->device_id, 'attrib_type' => 'ipmi_kg_key', 'attrib_value' => (string) $secretData['kg_key']];
                            }
                        } catch (\Throwable) {
                            // Ignore decryption failure on rollback
                        }
                    }
                }
                if (! empty($attribs)) {
                    DB::table('devices_attribs')->insert($attribs);
                }
            });
    }
};
