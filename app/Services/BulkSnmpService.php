<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;

/**
 * Bulk SNMP credentials updater for Device Groups.
 *
 * Provides validation, dry-run testing and atomic per-device update
 * of SNMP credentials for all devices in a Device Group.
 */
class BulkSnmpService
{
    /**
     * SNMP fields that this service is allowed to update.
     * Whitelisted to prevent accidental mass-edit of other fields.
     */
    public const ALLOWED_FIELDS = [
        'snmpver',
        'community',
        'authlevel',
        'authname',
        'authpass',
        'authalgo',
        'cryptopass',
        'cryptoalgo',
        'port',
        'transport',
    ];

    /**
     * Valid SNMPv3 authentication algorithms as understood by net-snmp/LibreNMS.
     */
    public const AUTH_ALGOS = ['MD5', 'SHA', 'SHA-224', 'SHA-256', 'SHA-384', 'SHA-512'];

    /**
     * Valid SNMPv3 privacy algorithms.
     */
    public const PRIV_ALGOS = ['DES', 'AES', 'AES-192', 'AES-256'];

    /**
     * Valid SNMPv3 security levels.
     */
    public const SECURITY_LEVELS = ['noAuthNoPriv', 'authNoPriv', 'authPriv'];

    /**
     * Build a sanitized array of SNMP update fields from validated input.
     *
     * Only fields present and allowed will be returned, so partial updates
     * (e.g. only rotate passwords) are possible.
     *
     * @param  array<string,mixed>  $input
     * @return array<string,mixed>
     */
    public function buildUpdateFields(array $input): array
    {
        $fields = [];
        foreach (self::ALLOWED_FIELDS as $key) {
            if (array_key_exists($key, $input) && $input[$key] !== null && $input[$key] !== '') {
                $fields[$key] = $input[$key];
            }
        }

        return $fields;
    }

    /**
     * Test a set of SNMP credentials against every device in the collection.
     * Runs a synchronous SNMP get on `sysObjectID.0` per device.
     *
     * @param  Collection<int,Device>  $devices
     * @param  array<string,mixed>  $credentials
     * @return array<int,array{device_id:int,hostname:string,success:bool,message:string}>
     */
    public function testCredentials(Collection $devices, array $credentials): array
    {
        $results = [];

        foreach ($devices as $device) {
            $result = $this->testSingleDevice($device, $credentials);
            $results[] = [
                'device_id' => $device->device_id,
                'hostname' => $device->hostname,
                'success' => $result['success'],
                'message' => $result['message'],
            ];
        }

        return $results;
    }

    /**
     * Apply credentials to every device in the collection.
     * Each device update is wrapped in its own try/catch so a single failure
     * does not abort the rest. Eventlog entries are written per device.
     *
     * @param  Collection<int,Device>  $devices
     * @param  array<string,mixed>  $fields
     * @return array{success:array<int,array<string,mixed>>,failed:array<int,array<string,mixed>>}
     */
    public function applyCredentials(Collection $devices, array $fields): array
    {
        $success = [];
        $failed = [];

        // Strip non-allowed fields as a defense in depth measure.
        $fields = array_intersect_key($fields, array_flip(self::ALLOWED_FIELDS));

        if (empty($fields)) {
            return ['success' => [], 'failed' => []];
        }

        foreach ($devices as $device) {
            try {
                // forceFill() bypasses Laravel mass-assignment protection.
                // Safe here: $fields is already whitelisted against ALLOWED_FIELDS above.
                $device->forceFill($fields);
                $device->save();

                Eventlog::log(
                    sprintf(
                        'Bulk SNMP update: fields [%s] changed by %s',
                        implode(', ', array_keys($fields)),
                        Auth::user()->username ?? 'system'
                    ),
                    $device->device_id,
                    'system',
                    Severity::Notice
                );

                $success[] = [
                    'device_id' => $device->device_id,
                    'hostname' => $device->hostname,
                ];
            } catch (\Throwable $e) {
                Log::error('BulkSnmp apply failed for device ' . $device->device_id . ': ' . $e->getMessage());

                $failed[] = [
                    'device_id' => $device->device_id,
                    'hostname' => $device->hostname,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * Perform a single SNMP test against a device using the given credentials.
     * Falls back to the device's existing fields for any value not supplied.
     *
     * @param  array<string,mixed>  $credentials
     * @return array{success:bool,message:string}
     */
    protected function testSingleDevice(Device $device, array $credentials): array
    {
        // Build a temporary device snapshot with the new creds applied,
        // without persisting any change to the database.
        $original = $device->getOriginal();

        try {
            // forceFill() bypasses mass-assignment protection; whitelisted above.
            $device->forceFill(array_intersect_key($credentials, array_flip(self::ALLOWED_FIELDS)));

            $oid = '.1.3.6.1.2.1.1.2.0'; // sysObjectID.0
            $result = \SnmpQuery::device($device)->get($oid);

            $value = $result->value();
            $success = ! empty($value);

            return [
                'success' => $success,
                'message' => $success ? 'SNMP reachable' : 'No response or auth failure',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            // Restore original attributes; never persist the test attempt.
            foreach ($original as $key => $value) {
                $device->setAttribute($key, $value);
            }
        }
    }
}
