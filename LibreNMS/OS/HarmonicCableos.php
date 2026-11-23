<?php

namespace LibreNMS\OS;

use LibreNMS\OS;
use LibreNMS\Util\Mac;
use SnmpQuery;

class HarmonicCableos extends OS
{
    // CableOS 3.29 SNMP User Guide, Table 3-135 (hrmCmtsRpdTable).
    // Numeric OIDs keep discovery independent of the unbundled Harmonic MIB.
    private const RPD_ENTRY = '.1.3.6.1.4.1.1563.10.2.3.116.1.7.1.1';
    private const RPD_MAC_ADDRESS = self::RPD_ENTRY . '.2';
    private const RPD_OPER_STATUS = self::RPD_ENTRY . '.3';
    private const UNKNOWN_STATE = -1;

    public function rpdStateDiscoveryData(): array
    {
        $rows = SnmpQuery::options('-OQx')->numeric()->displayHints(false)->walk(self::RPD_ENTRY)->throwIfConnectionError();
        $macs = $rows->pluck(preg_quote(self::RPD_MAC_ADDRESS, '/'));
        $states = $rows->pluck(preg_quote(self::RPD_OPER_STATUS, '/'));
        $data = [];

        foreach ($macs as $index => $value) {
            $mac = Mac::parseSnmp($value);
            if (! $mac->isValid() || $mac->hex() === '000000000000') {
                continue;
            }

            $data[$index] = [
                'hrmCmtsRpdMacAddr' => $mac->readable(),
                'hrmCmtsRpdOperStatus' => self::rpdOperationalState(self::UNKNOWN_STATE, $states[$index] ?? ''),
            ];
        }

        return $data;
    }

    public static function rpdOperationalState(int|float|string $value, ?string $raw = null): int
    {
        // The shared poller coerces an empty sample to zero and compares null loosely
        // with zero. Use a local Unknown translation outside the vendor's 0–6 range.
        $raw ??= (string) $value;

        return match ($raw) {
            '0', '1', '2', '3', '4', '5', '6' => (int) $raw,
            default => self::UNKNOWN_STATE,
        };
    }
}
