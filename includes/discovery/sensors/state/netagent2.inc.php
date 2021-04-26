<?php
/**
 * netagent2.inc.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * 3 Phase support extension
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */
$ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.4.1.1.0';
$ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

if (! empty($ups_state) || $ups_state == 0) {
    // UPS state OID (Value : 0-1 Unknown, 2 On Line, 3 On Battery, 4 On Boost, 5 Sleeping, 6 On Bypass, 7 Rebooting, 8 Standby, 9 On Buck )
    $state_name = 'netagent2upsstate';
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'OnLine'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'OnBattery'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'OnBoost'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'Sleeping'],
        ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'OnBypass'],
        ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'Rebooting'],
        ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'Standby'],
        ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'OnBuck'],
    ];
    create_state_index($state_name, $states);

    $index = 0;
    $limit = 10;
    $warnlimit = null;
    $lowlimit = null;
    $lowwarnlimit = null;
    $divisor = 1;
    $state = $ups_state / $divisor;
    $descr = 'UPS state';

    discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
    create_sensor_to_state_index($device, $state_name, $index);
}

// Detect type of UPS (Signle-Phase/3 Phase)
// Number of input lines
$upsInputNumLines_oid = '.1.3.6.1.2.1.33.1.3.2.0';
$in_phaseNum = snmp_get($device, $upsInputNumLines_oid, '-Oqv');

// 3 Phase system states
if ($in_phaseNum == '3') {
    // In And Out
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.4.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusInAndOut';
        $states = [
            ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'threeInOneOut'],
            ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'threeInThreeOut'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'In And Out';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Back Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.5.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusBatteryStatus';
        $states = [
            ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'backup'],
            ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'acnormal'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Back Status';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Charge Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.6.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusChargeStatus';
        $states = [
            ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'boost'],
            ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'float'],
            ['value' => 16, 'generic' => 2, 'graph' => 0, 'descr' => 'no'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Charge Status';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Bypass braker status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.2.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusManualBypassBreaker';
        $states = [
            ['value' => 8, 'generic' => 1, 'graph' => 0, 'descr' => 'close'],
            ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'open'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Breaker Status';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // AC Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.3.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusACStatus';
        $states = [
            ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
            ['value' => 11, 'generic' => 2, 'graph' => 0, 'descr' => 'abnormal'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'AC status';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Common State - Inverter active, Rectifier Operating
    $states = [
        ['value' => 14, 'generic' => 0, 'graph' => 0, 'descr' => 'yes'],
        ['value' => 16, 'generic' => 2, 'graph' => 0, 'descr' => 'no'],
    ];

    // Inverter active
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.5.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusInverterOperating';
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Inverter Operating';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Rectifier Operating
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.7.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusRecOperating';
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Rectifier Operating';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Switch Mode
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.4.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStaticSwitchMode';
        $states = [
            ['value' => 12, 'generic' => 0, 'graph' => 0, 'descr' => 'invermode'],
            ['value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'bypassmode'],
        ];
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Switch Mode';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Common State - Rectifier Rotation Error, Bypass Status and Short Circuit
    $states = [
        ['value' => 14, 'generic' => 2, 'graph' => 0, 'descr' => 'yes'],
        ['value' => 16, 'generic' => 0, 'graph' => 0, 'descr' => 'no'],
    ];

    // Rectifier Rotation Error
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.1.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusRecRotError';
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Rectifier Rotation Error';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Bypass Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.1.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusBypassFreqFail';
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Bypass freq. fail';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }

    // Short Circuit
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.7.7.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (! empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseFaultStatusShortCircuit';
        create_state_index($state_name, $states);

        $index = 0;
        $limit = 10;
        $warnlimit = null;
        $lowlimit = null;
        $lowwarnlimit = null;
        $divisor = 1;
        $state = $ups_state / $divisor;
        $descr = 'Short Circuit';

        discover_sensor($valid['sensor'], 'state', $device, $ups_state_oid, $index, $state_name, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $warnlimit, $limit, $state);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
