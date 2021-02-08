<?php
/**
 * fs-nmu.inc.php
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
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */
echo 'FS NMU Signals';

// SLOT A
$a1_tx = snmp_get($device, 'vSFPA1TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$a1_rx = snmp_get($device, 'vSFPA1RxPower.0', '-Ovqe', 'OAP-C1-OEO');
$a2_tx = snmp_get($device, 'vSFPA2TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$a2_rx = snmp_get($device, 'vSFPA2RxPower.0', '-Ovqe', 'OAP-C1-OEO');
// SLOT B
$b1_tx = snmp_get($device, 'vSFPB1TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$b1_rx = snmp_get($device, 'vSFPB1RxPower.0', '-Ovqe', 'OAP-C1-OEO');
$b2_tx = snmp_get($device, 'vSFPB2TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$b2_rx = snmp_get($device, 'vSFPB2RxPower.0', '-Ovqe', 'OAP-C1-OEO');
// SLOT C
$c1_tx = snmp_get($device, 'vSFPC1TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$c1_rx = snmp_get($device, 'vSFPC1RxPower.0', '-Ovqe', 'OAP-C1-OEO');
$c2_tx = snmp_get($device, 'vSFPC2TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$c2_rx = snmp_get($device, 'vSFPC2RxPower.0', '-Ovqe', 'OAP-C1-OEO');
// SLOT D
$d1_tx = snmp_get($device, 'vSFPD1TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$d1_rx = snmp_get($device, 'vSFPD1RxPower.0', '-Ovqe', 'OAP-C1-OEO');
$d2_tx = snmp_get($device, 'vSFPD2TxPower.0', '-Ovqe', 'OAP-C1-OEO');
$d2_rx = snmp_get($device, 'vSFPD2RxPower.0', '-Ovqe', 'OAP-C1-OEO');
// SLOT A
$oid_a1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.11.4.0';
$oid_a1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.11.5.0';
$oid_a2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.12.4.0';
$oid_a2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.12.5.0';
// SLOT B
$oid_b1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.13.4.0';
$oid_b1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.13.5.0';
$oid_b2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.14.4.0';
$oid_b2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.14.5.0';
// SLOT C
$oid_c1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.15.4.0';
$oid_c1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.15.5.0';
$oid_c2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.16.4.0';
$oid_c2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.16.5.0';
// SLOT D
$oid_d1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.17.4.0';
$oid_d1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.17.5.0';
$oid_d2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.18.4.0';
$oid_d2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.18.5.0';

// Discover A1 TX Sensor
if (is_numeric($a1_tx)) {
    $descr = 'A1 Tx Power';
    $index = 'vSFPA1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_a1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $a1_tx,
        'snmp'
    );
}

// Discover A1 RX Sensor
if (is_numeric($a1_rx)) {
    $descr = 'A1 Rx Power';
    $index = 'vSFPA1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_a1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $a1_rx,
        'snmp'
    );
}

// Discover A2 TX Sensor
if (is_numeric($a2_tx)) {
    $descr = 'A2 Tx Power';
    $index = 'vSFPA2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_a2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $a2_tx,
        'snmp'
    );
}

// Discover A2 RX Sensor
if (is_numeric($a2_rx)) {
    $descr = 'A2 Rx Power';
    $index = 'vSFPA2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_a2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $a2_rx,
        'snmp'
    );
}

// Discover B1 TX Sensor
if (is_numeric($b1_tx)) {
    $descr = 'B1 Tx Power';
    $index = 'vSFPB1TxPower.0';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_b1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $b1_tx,
        'snmp'
    );
}

// Discover B1 RX Sensor
if (is_numeric($b1_rx)) {
    $descr = 'B1 Rx Power';
    $index = 'vSFPB1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_b1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $b1_rx,
        'snmp'
    );
}

// Discover B2 TX Sensor
if (is_numeric($b2_tx)) {
    $descr = 'B2 Tx Power';
    $index = 'vSFPB2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $b2_tx,
        'snmp'
    );
}

// Discover B2 RX Sensor
if (is_numeric($b2_rx)) {
    $descr = 'B2 Rx Power';
    $index = 'vSFPB2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $b2_tx,
        'snmp'
    );
}

// Discover C1 TX Sensor
if (is_numeric($c1_tx)) {
    $descr = 'C1 Tx Power';
    $index = 'vSFPC1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_c1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_tx,
        'snmp'
    );
}

// Discover C1 RX Sensor
if (is_numeric($c1_rx)) {
    $descr = 'A1 Rx Power';
    $index = 'vSFPC1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_c1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_rx,
        'snmp'
    );
}

// Discover C2 TX Sensor
if (is_numeric($c2_tx)) {
    $descr = 'C2 Tx Power';
    $index = 'vSFPC2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_tx,
        'snmp'
    );
}

// Discover C2 RX Sensor
if (is_numeric($c2_rx)) {
    $descr = 'C2 Rx Power';
    $index = 'vSFPC2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_tx,
        'snmp'
    );
}

// Discover D1 TX Sensor
if (is_numeric($d1_tx)) {
    $descr = 'D1 Tx Power';
    $index = 'vSFPD1TxPower.0';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_d1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $d1_tx,
        'snmp'
    );
}

// Discover D1 RX Sensor
if (is_numeric($d1_rx)) {
    $descr = 'B1 Rx Power';
    $index = 'vSFPD1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_d1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $d1_rx,
        'snmp'
    );
}

// Discover D2 TX Sensor
if (is_numeric($d2_tx)) {
    $descr = 'D2 Tx Power';
    $index = 'vSFPD2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_d2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $d2_tx,
        'snmp'
    );
}

// Discover D2 RX Sensor
if (is_numeric($d2_rx)) {
    $descr = 'D2 Rx Power';
    $index = 'vSFPD2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        $valid['sensor'],
        'dbm',
        $device,
        $oid_d2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $d2_rx,
        'snmp'
    );
}
