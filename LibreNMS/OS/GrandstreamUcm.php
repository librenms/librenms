<?php

namespace LibreNMS\OS;

use LibreNMS\OS;

class GrandstreamUcm extends OS
{
    public const UNKNOWN = 7;
    public const TRUNK_TABLE = 'GS-UCM63XX-SNMP-MIB::sTrunksTable';
    public const TRUNK_STATUS = 'GS-UCM63XX-SNMP-MIB::sTrunksStatus';
    public const TRUNK_NAME = 'GS-UCM63XX-SNMP-MIB::sTrunksName';
    public const TRUNK_OID_PREFIX = '.1.3.6.1.4.1.12581.2.5.1.1.4.';

    public static function trunkState(?string $value): int
    {
        // Preserve existing state IDs and the meaning of historical measurements.
        return match (strtolower(trim($value ?? ''))) {
            'registered' => 0,
            'reachable' => 1,
            'available' => 2,
            'disabled' => 3,
            'unmonitored' => 4,
            'unregistered' => 5,
            'unreachable' => 6,
            default => self::UNKNOWN,
        };
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $rows
     * @param  iterable<\App\Models\Sensor>  $preserve
     * @return array<int|string, array<string, mixed>>
     */
    public static function normalizeTrunks(array $rows, iterable $preserve = []): array
    {
        foreach ($rows as $index => $row) {
            $rows[$index][self::TRUNK_STATUS] = self::trunkState($row[self::TRUNK_STATUS] ?? null);
        }

        // Preserve missing rows only when the caller observed a failed SNMP request.
        foreach ($preserve as $sensor) {
            if ($sensor->sensor_class !== 'state' || $sensor->sensor_type !== 'grandstreamUcmTrunkStatus'
                || $sensor->sensor_deleted || $sensor->poller_type !== 'snmp'
                || ! str_starts_with($sensor->sensor_oid, self::TRUNK_OID_PREFIX)) {
                continue;
            }

            $index = substr($sensor->sensor_oid, strlen(self::TRUNK_OID_PREFIX));
            if ($index === '' || ! ctype_digit($index) || isset($rows[$index])) {
                continue;
            }

            $rows[$index] = [self::TRUNK_NAME => $sensor->sensor_descr, self::TRUNK_STATUS => self::UNKNOWN];
        }

        return $rows;
    }
}
