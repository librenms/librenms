<?php
/**
 * fs-nmu.inc.php
 *
 * OAP OEO and EDFA Modules for FibreSwitches NMUs
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
 *
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 * @copyright  2022 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 */
$oap_dbm_multiplier = 1;
$oap_dbm_divisor = 100;
$oap_flags = '-Ovqe';

$channel_wavelengths = [
    '157703' => 'Ch.1',
    '157620' => 'Ch.2',
    '157537' => 'Ch.3',
    '157454' => 'Ch.4',
    '157371' => 'Ch.5',
    '157289' => 'Ch.6',
    '157206' => 'Ch.7',
    '157124' => 'Ch.8',
    '157042' => 'Ch.9',
    '156959' => 'Ch.10',
    '156811' => 'Ch.11',
    '156795' => 'Ch.12',
    '156713' => 'Ch.13',
    '156631' => 'Ch.14',
    '156550' => 'Ch.15',
    '156468' => 'Ch.16',
    '156386' => 'Ch.17',
    '156305' => 'Ch.18',
    '156223' => 'Ch.19',
    '156142' => 'Ch.20',
    '156061' => 'Ch.21',
    '155979' => 'Ch.22',
    '155898' => 'Ch.23',
    '155817' => 'Ch.24',
    '155736' => 'Ch.25',
    '155655' => 'Ch.26',
    '155575' => 'Ch.27',
    '155494' => 'Ch.28',
    '155413' => 'Ch.29',
    '155333' => 'Ch.30',
    '155252' => 'Ch.31',
    '155172' => 'Ch.32',
    '155092' => 'Ch.33',
    '155012' => 'Ch.34',
    '154932' => 'Ch.35',
    '154852' => 'Ch.36',
    '154772' => 'Ch.37',
    '154692' => 'Ch.38',
    '154612' => 'Ch.39',
    '154532' => 'Ch.40',
    '154453' => 'Ch.41',
    '154373' => 'Ch.42',
    '154294' => 'Ch.43',
    '154214' => 'Ch.44',
    '154135' => 'Ch.45',
    '154056' => 'Ch.46',
    '153977' => 'Ch.47',
    '153898' => 'Ch.48',
    '153819' => 'Ch.49',
    '153740' => 'Ch.50',
    '153661' => 'Ch.51',
    '153582' => 'Ch.52',
    '153504' => 'Ch.53',
    '153425' => 'Ch.54',
    '153347' => 'Ch.55',
    '153268' => 'Ch.56',
    '153190' => 'Ch.57',
    '153112' => 'Ch.58',
    '153033' => 'Ch.59',
    '152955' => 'Ch.60',
    '152877' => 'Ch.61',
    '152799' => 'Ch.62',
    '152722' => 'Ch.63',
    '152644' => 'Ch.64',
    '152566' => 'Ch.65',
    '152489' => 'Ch.66',
    '152411' => 'Ch.67',
    '152334' => 'Ch.68',
    '152256' => 'Ch.69',
    '152179' => 'Ch.70',
    '152102' => 'Ch.71',
    '152025' => 'Ch.72',
];

echo "FS NMU OEO Light Levels (dbm)\n";

// OAP C1 -> C16 OEO Modules
$oap_oeos = range(1, 16);
$oap_oeo_sensors = [
    'TxPower' => ['desc' => 'Tx Power', 'id' => '4',
        'limits' => [
            // 10km Optics Transmit Limits
            '10000' => [
                'low_limit' => -8.2,
                'low_warn_limit' => -7.2,
                'warn_limit' => 0.25,
                'high_limit' => 0.5,
            ],
            // 80km Optics Transmit Limits
            '80000' => [
                'low_limit' => 0,
                'low_warn_limit' => 1,
                'warn_limit' => 3.75,
                'high_limit' => 4,
            ],
        ],
    ],
    'RxPower' => ['desc' => 'Rx Power', 'id' => '5',
        'limits' => [
            // 10km Optics Receive Limits
            '10000' => [
                'low_limit' => -14.4,
                'low_warn_limit' => -11.4,
                'warn_limit' => -0.5,
                'high_limit' => 0.5,
            ],
            // 80km Optics eceive Limits
            '80000' => [
                'low_limit' => -23,
                'low_warn_limit' => -20,
                'warn_limit' => -10,
                'high_limit' => -7,
            ],
        ],
    ],
];

foreach ($oap_oeos as $oap_oeo) {
    $object_ident = 'OAP-C' . $oap_oeo . '-OEO';

    // Slots in OEO for optics pairs
    $oap_oeo_slots = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2'];
    $oeo_offset = 11;

    foreach ($oap_oeo_slots as $slot) {
        $mode_wave = snmp_get($device, 'vSFP' . $slot . 'ModeWave.0', $oap_flags, $object_ident);
        if (is_numeric($mode_wave)) {
            $dwdm_ch = '';
            if (isset($channel_wavelengths[$mode_wave])) {
                $dwdm_ch = $channel_wavelengths[$mode_wave] . ' ';
            }
            $mode_wave = $dwdm_ch . '(' . strval($mode_wave / 100) . 'nm)';
            $tx_distance = snmp_get($device, 'vSFP' . $slot . 'ModeTransmissionDistance.0', $oap_flags, $object_ident);
            foreach ($oap_oeo_sensors as $sensor => $options) {
                $object_type = 'vSFP' . $slot . $sensor . '.0';
                $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);
                if (is_numeric($dbm_value)) {
                    $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_oeo . '.2.' . $oeo_offset . '.' . $options['id'] . '.0';
                    $sensor_description = 'C' . $oap_oeo . ' OEO ' . $slot . ' ' . $mode_wave . ' ' . $options['desc'];
                    $index = $device['device_id'] . '::' . $object_ident . '::' . $object_type;

                    discover_sensor(
                        $valid['sensor'],
                        'dbm',
                        $device,
                        $sensor_oid,
                        $index,
                        'fs-nmu',
                        $sensor_description,
                        $oap_dbm_divisor,
                        $oap_dbm_multiplier,
                        $options['limits'][$tx_distance]['low_limit'],
                        $options['limits'][$tx_distance]['low_warn_limit'],
                        $options['limits'][$tx_distance]['warn_limit'],
                        $options['limits'][$tx_distance]['high_limit'],
                        $dbm_value,
                        'snmp',
                        null, null, null,
                        $object_ident
                    );
                }
            }
        }
        $oeo_offset++;
    }
}

echo "FS NMU EDFAs Levels (dbm)\n";

// OAP C1 -> C16 EDFAs
$oap_edfas = range(1, 16);
$oap_edfa_sensors = [
    'PUMPPower' => ['desc' => 'Pump Power', 'id' => '24',
        'limits' => [
            'low_limit' => 12.5,
            'low_warn_limit' => 16,
            'warn_limit' => 18,
            'high_limit' => 21.5,
        ],
    ],
    'Input' => ['desc' => 'Input Power', 'id' => '28',
        'limits' => [
            'low_limit' => -23,
            'low_warn_limit' => -22,
            'warn_limit' => 11,
            'high_limit' => 12,
        ],
    ],
    'Output' => ['desc' => 'Output Power', 'id' => '29',
        'limits' => [
            'low_limit' => -6,
            'low_warn_limit' => -5,
            'warn_limit' => 25,
            'high_limit' => 29,
        ],
    ],
];

foreach ($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';

    foreach ($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor . '.0';
        $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);

        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' . $options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            $index = $device['device_id'] . '::' . $object_ident . '::' . $object_type;

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $sensor_oid,
                $index,
                'fs-nmu',
                $sensor_description,
                $oap_dbm_divisor,
                $oap_dbm_multiplier,
                $options['limits']['low_limit'],
                $options['limits']['low_warn_limit'],
                $options['limits']['warn_limit'],
                $options['limits']['high_limit'],
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
            );
        } else {
            break;
        }
    }
}
