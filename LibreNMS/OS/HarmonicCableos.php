<?php

namespace LibreNMS\OS;

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
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

    public function discoverRpdStates(): void
    {
        $rows = SnmpQuery::options('-OQx')->numeric()->displayHints(false)->walk(self::RPD_ENTRY)->throwIfConnectionError();
        $macs = $rows->pluck(preg_quote(self::RPD_MAC_ADDRESS, '/'));
        $states = $rows->pluck(preg_quote(self::RPD_OPER_STATUS, '/'));
        $discovery = app('sensor-discovery');

        $discovery->withStateTranslations('hrmCmtsRpdOperStatus', [
            StateTranslation::define('Unknown', self::UNKNOWN_STATE, Severity::Unknown),
            StateTranslation::define('Offline', 0, Severity::Error),
            StateTranslation::define('Identifying', 1, Severity::Warning),
            StateTranslation::define('Configuring', 2, Severity::Warning),
            StateTranslation::define('Online', 3, Severity::Ok),
            StateTranslation::define('Identification failed', 4, Severity::Error),
            StateTranslation::define('Configuration failed', 5, Severity::Error),
            StateTranslation::define('Discovered', 6, Severity::Warning),
        ]);

        foreach ($macs as $index => $value) {
            $mac = Mac::parseSnmp($value);
            if (! $mac->isValid() || $mac->hex() === '000000000000') {
                continue;
            }

            $discovery->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => self::RPD_OPER_STATUS . '.' . $index,
                'sensor_index' => $index,
                'sensor_type' => 'hrmCmtsRpdOperStatus',
                'sensor_descr' => 'RPD ' . $mac->readable(),
                'sensor_current' => self::rpdOperationalState(0, $states[$index] ?? ''),
                'group' => 'Remote PHY Devices',
                'user_func' => self::class . '::rpdOperationalState',
            ]));
        }
    }

    public static function rpdOperationalState(int|float $value, string $raw): int
    {
        // The shared poller coerces an empty sample to zero and compares null loosely
        // with zero. Use a local Unknown translation outside the vendor's 0–6 range.
        return match ($raw) {
            '0', '1', '2', '3', '4', '5', '6' => (int) $raw,
            default => self::UNKNOWN_STATE,
        };
    }
}
