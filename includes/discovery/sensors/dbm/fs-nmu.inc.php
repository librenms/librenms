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
 *
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */
echo 'FS NMU Signals';

// CARD 1 SLOT A
$c1_a1_tx = SnmpQuery::get('OAP-C1-OEO::vSFPA1TxPower.0')->value();
$c1_a1_rx = SnmpQuery::get('OAP-C1-OEO::vSFPA1RxPower.0')->value();
$c1_a2_tx = SnmpQuery::get('OAP-C1-OEO::vSFPA2TxPower.0')->value();
$c1_a2_rx = SnmpQuery::get('OAP-C1-OEO::vSFPA2RxPower.0')->value();
// CARD 1 SLOT B
$c1_b1_tx = SnmpQuery::get('OAP-C1-OEO::vSFPB1TxPower.0')->value();
$c1_b1_rx = SnmpQuery::get('OAP-C1-OEO::vSFPB1RxPower.0')->value();
$c1_b2_tx = SnmpQuery::get('OAP-C1-OEO::vSFPB2TxPower.0')->value();
$c1_b2_rx = SnmpQuery::get('OAP-C1-OEO::vSFPB2RxPower.0')->value();
// CARD 1 SLOT C
$c1_c1_tx = SnmpQuery::get('OAP-C1-OEO::vSFPC1TxPower.0')->value();
$c1_c1_rx = SnmpQuery::get('OAP-C1-OEO::vSFPC1RxPower.0')->value();
$c1_c2_tx = SnmpQuery::get('OAP-C1-OEO::vSFPC2TxPower.0')->value();
$c1_c2_rx = SnmpQuery::get('OAP-C1-OEO::vSFPC2RxPower.0')->value();
// CARD 1 SLOT D
$c1_d1_tx = SnmpQuery::get('OAP-C1-OEO::vSFPD1TxPower.0')->value();
$c1_d1_rx = SnmpQuery::get('OAP-C1-OEO::vSFPD1RxPower.0')->value();
$c1_d2_tx = SnmpQuery::get('OAP-C1-OEO::vSFPD2TxPower.0')->value();
$c1_d2_rx = SnmpQuery::get('OAP-C1-OEO::vSFPD2RxPower.0')->value();
// CARD 1 SLOT A
$oid_c1_a1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.11.4.0';
$oid_c1_a1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.11.5.0';
$oid_c1_a2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.12.4.0';
$oid_c1_a2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.12.5.0';
// CARD 1 SLOT B
$oid_c1_b1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.13.4.0';
$oid_c1_b1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.13.5.0';
$oid_c1_b2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.14.4.0';
$oid_c1_b2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.14.5.0';
// CARD 1 SLOT C
$oid_c1_c1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.15.4.0';
$oid_c1_c1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.15.5.0';
$oid_c1_c2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.16.4.0';
$oid_c1_c2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.16.5.0';
// CARD 1 SLOT D
$oid_c1_d1_tx = '.1.3.6.1.4.1.40989.10.16.1.2.17.4.0';
$oid_c1_d1_rx = '.1.3.6.1.4.1.40989.10.16.1.2.17.5.0';
$oid_c1_d2_tx = '.1.3.6.1.4.1.40989.10.16.1.2.18.4.0';
$oid_c1_d2_rx = '.1.3.6.1.4.1.40989.10.16.1.2.18.5.0';

// CARD 2 SLOT A
$c2_a1_tx = SnmpQuery::get('OAP-C2-OEO::vSFPA1TxPower.0')->value();
$c2_a1_rx = SnmpQuery::get('OAP-C2-OEO::vSFPA1RxPower.0')->value();
$c2_a2_tx = SnmpQuery::get('OAP-C2-OEO::vSFPA2TxPower.0')->value();
$c2_a2_rx = SnmpQuery::get('OAP-C2-OEO::vSFPA2RxPower.0')->value();
// CARD 2 SLOT B
$c2_b1_tx = SnmpQuery::get('OAP-C2-OEO::vSFPB1TxPower.0')->value();
$c2_b1_rx = SnmpQuery::get('OAP-C2-OEO::vSFPB1RxPower.0')->value();
$c2_b2_tx = SnmpQuery::get('OAP-C2-OEO::vSFPB2TxPower.0')->value();
$c2_b2_rx = SnmpQuery::get('OAP-C2-OEO::vSFPB2RxPower.0')->value();
// CARD 2 SLOT C
$c2_c1_tx = SnmpQuery::get('OAP-C2-OEO::vSFPC1TxPower.0')->value();
$c2_c1_rx = SnmpQuery::get('OAP-C2-OEO::vSFPC1RxPower.0')->value();
$c2_c2_tx = SnmpQuery::get('OAP-C2-OEO::vSFPC2TxPower.0')->value();
$c2_c2_rx = SnmpQuery::get('OAP-C2-OEO::vSFPC2RxPower.0')->value();
// CARD 2 SLOT D
$c2_d1_tx = SnmpQuery::get('OAP-C2-OEO::vSFPD1TxPower.0')->value();
$c2_d1_rx = SnmpQuery::get('OAP-C2-OEO::vSFPD1RxPower.0')->value();
$c2_d2_tx = SnmpQuery::get('OAP-C2-OEO::vSFPD2TxPower.0')->value();
$c2_d2_rx = SnmpQuery::get('OAP-C2-OEO::vSFPD2RxPower.0')->value();
// CARD 2 SLOT A
$oid_c2_a1_tx = '.1.3.6.1.4.1.40989.10.16.2.2.11.4.0';
$oid_c2_a1_rx = '.1.3.6.1.4.1.40989.10.16.2.2.11.5.0';
$oid_c2_a2_tx = '.1.3.6.1.4.1.40989.10.16.2.2.12.4.0';
$oid_c2_a2_rx = '.1.3.6.1.4.1.40989.10.16.2.2.12.5.0';
// CARD 2 SLOT B
$oid_c2_b1_tx = '.1.3.6.1.4.1.40989.10.16.2.2.13.4.0';
$oid_c2_b1_rx = '.1.3.6.1.4.1.40989.10.16.2.2.13.5.0';
$oid_c2_b2_tx = '.1.3.6.1.4.1.40989.10.16.2.2.14.4.0';
$oid_c2_b2_rx = '.1.3.6.1.4.1.40989.10.16.2.2.14.5.0';
// CARD 2 SLOT C
$oid_c2_c1_tx = '.1.3.6.1.4.1.40989.10.16.2.2.15.4.0';
$oid_c2_c1_rx = '.1.3.6.1.4.1.40989.10.16.2.2.15.5.0';
$oid_c2_c2_tx = '.1.3.6.1.4.1.40989.10.16.2.2.16.4.0';
$oid_c2_c2_rx = '.1.3.6.1.4.1.40989.10.16.2.2.16.5.0';
// CARD 2 SLOT D
$oid_c2_d1_tx = '.1.3.6.1.4.1.40989.10.16.2.2.17.4.0';
$oid_c2_d1_rx = '.1.3.6.1.4.1.40989.10.16.2.2.17.5.0';
$oid_c2_d2_tx = '.1.3.6.1.4.1.40989.10.16.2.2.18.4.0';
$oid_c2_d2_rx = '.1.3.6.1.4.1.40989.10.16.2.2.18.5.0';

// CARD 3 SLOT A
$c3_a1_tx = SnmpQuery::get('OAP-C3-OEO::vSFPA1TxPower.0')->value();
$c3_a1_rx = SnmpQuery::get('OAP-C3-OEO::vSFPA1RxPower.0')->value();
$c3_a2_tx = SnmpQuery::get('OAP-C3-OEO::vSFPA2TxPower.0')->value();
$c3_a2_rx = SnmpQuery::get('OAP-C3-OEO::vSFPA2RxPower.0')->value();
// CARD 3 SLOT B
$c3_b1_tx = SnmpQuery::get('OAP-C3-OEO::vSFPB1TxPower.0')->value();
$c3_b1_rx = SnmpQuery::get('OAP-C3-OEO::vSFPB1RxPower.0')->value();
$c3_b2_tx = SnmpQuery::get('OAP-C3-OEO::vSFPB2TxPower.0')->value();
$c3_b2_rx = SnmpQuery::get('OAP-C3-OEO::vSFPB2RxPower.0')->value();
// CARD 3 SLOT C
$c3_c1_tx = SnmpQuery::get('OAP-C3-OEO::vSFPC1TxPower.0')->value();
$c3_c1_rx = SnmpQuery::get('OAP-C3-OEO::vSFPC1RxPower.0')->value();
$c3_c2_tx = SnmpQuery::get('OAP-C3-OEO::vSFPC2TxPower.0')->value();
$c3_c2_rx = SnmpQuery::get('OAP-C3-OEO::vSFPC2RxPower.0')->value();
// CARD 3 SLOT D
$c3_d1_tx = SnmpQuery::get('OAP-C3-OEO::vSFPD1TxPower.0')->value();
$c3_d1_rx = SnmpQuery::get('OAP-C3-OEO::vSFPD1RxPower.0')->value();
$c3_d2_tx = SnmpQuery::get('OAP-C3-OEO::vSFPD2TxPower.0')->value();
$c3_d2_rx = SnmpQuery::get('OAP-C3-OEO::vSFPD2RxPower.0')->value();
// CARD 3 SLOT A
$oid_c3_a1_tx = '.1.3.6.1.4.1.40989.10.16.3.2.11.4.0';
$oid_c3_a1_rx = '.1.3.6.1.4.1.40989.10.16.3.2.11.5.0';
$oid_c3_a2_tx = '.1.3.6.1.4.1.40989.10.16.3.2.12.4.0';
$oid_c3_a2_rx = '.1.3.6.1.4.1.40989.10.16.3.2.12.5.0';
// CARD 3 SLOT B
$oid_c3_b1_tx = '.1.3.6.1.4.1.40989.10.16.3.2.13.4.0';
$oid_c3_b1_rx = '.1.3.6.1.4.1.40989.10.16.3.2.13.5.0';
$oid_c3_b2_tx = '.1.3.6.1.4.1.40989.10.16.3.2.14.4.0';
$oid_c3_b2_rx = '.1.3.6.1.4.1.40989.10.16.3.2.14.5.0';
// CARD 3 SLOT C
$oid_c3_c1_tx = '.1.3.6.1.4.1.40989.10.16.3.2.15.4.0';
$oid_c3_c1_rx = '.1.3.6.1.4.1.40989.10.16.3.2.15.5.0';
$oid_c3_c2_tx = '.1.3.6.1.4.1.40989.10.16.3.2.16.4.0';
$oid_c3_c2_rx = '.1.3.6.1.4.1.40989.10.16.3.2.16.5.0';
// CARD 3 SLOT D
$oid_c3_d1_tx = '.1.3.6.1.4.1.40989.10.16.3.2.17.4.0';
$oid_c3_d1_rx = '.1.3.6.1.4.1.40989.10.16.3.2.17.5.0';
$oid_c3_d2_tx = '.1.3.6.1.4.1.40989.10.16.3.2.18.4.0';
$oid_c3_d2_rx = '.1.3.6.1.4.1.40989.10.16.3.2.18.5.0';

// CARD 4 SLOT A
$c4_a1_tx = SnmpQuery::get('OAP-C4-OEO::vSFPA1TxPower.0')->value();
$c4_a1_rx = SnmpQuery::get('OAP-C4-OEO::vSFPA1RxPower.0')->value();
$c4_a2_tx = SnmpQuery::get('OAP-C4-OEO::vSFPA2TxPower.0')->value();
$c4_a2_rx = SnmpQuery::get('OAP-C4-OEO::vSFPA2RxPower.0')->value();
// CARD 4 SLOT B
$c4_b1_tx = SnmpQuery::get('OAP-C4-OEO::vSFPB1TxPower.0')->value();
$c4_b1_rx = SnmpQuery::get('OAP-C4-OEO::vSFPB1RxPower.0')->value();
$c4_b2_tx = SnmpQuery::get('OAP-C4-OEO::vSFPB2TxPower.0')->value();
$c4_b2_rx = SnmpQuery::get('OAP-C4-OEO::vSFPB2RxPower.0')->value();
// CARD 4 SLOT C
$c4_c1_tx = SnmpQuery::get('OAP-C4-OEO::vSFPC1TxPower.0')->value();
$c4_c1_rx = SnmpQuery::get('OAP-C4-OEO::vSFPC1RxPower.0')->value();
$c4_c2_tx = SnmpQuery::get('OAP-C4-OEO::vSFPC2TxPower.0')->value();
$c4_c2_rx = SnmpQuery::get('OAP-C4-OEO::vSFPC2RxPower.0')->value();
// CARD 4 SLOT D
$c4_d1_tx = SnmpQuery::get('OAP-C4-OEO::vSFPD1TxPower.0')->value();
$c4_d1_rx = SnmpQuery::get('OAP-C4-OEO::vSFPD1RxPower.0')->value();
$c4_d2_tx = SnmpQuery::get('OAP-C4-OEO::vSFPD2TxPower.0')->value();
$c4_d2_rx = SnmpQuery::get('OAP-C4-OEO::vSFPD2RxPower.0')->value();
// CARD 4 SLOT A
$oid_c4_a1_tx = '.1.3.6.1.4.1.40989.10.16.4.2.11.4.0';
$oid_c4_a1_rx = '.1.3.6.1.4.1.40989.10.16.4.2.11.5.0';
$oid_c4_a2_tx = '.1.3.6.1.4.1.40989.10.16.4.2.12.4.0';
$oid_c4_a2_rx = '.1.3.6.1.4.1.40989.10.16.4.2.12.5.0';
// CARD 4 SLOT B
$oid_c4_b1_tx = '.1.3.6.1.4.1.40989.10.16.4.2.13.4.0';
$oid_c4_b1_rx = '.1.3.6.1.4.1.40989.10.16.4.2.13.5.0';
$oid_c4_b2_tx = '.1.3.6.1.4.1.40989.10.16.4.2.14.4.0';
$oid_c4_b2_rx = '.1.3.6.1.4.1.40989.10.16.4.2.14.5.0';
// CARD 4 SLOT C
$oid_c4_c1_tx = '.1.3.6.1.4.1.40989.10.16.4.2.15.4.0';
$oid_c4_c1_rx = '.1.3.6.1.4.1.40989.10.16.4.2.15.5.0';
$oid_c4_c2_tx = '.1.3.6.1.4.1.40989.10.16.4.2.16.4.0';
$oid_c4_c2_rx = '.1.3.6.1.4.1.40989.10.16.4.2.16.5.0';
// CARD 4 SLOT D
$oid_c4_d1_tx = '.1.3.6.1.4.1.40989.10.16.4.2.17.4.0';
$oid_c4_d1_rx = '.1.3.6.1.4.1.40989.10.16.4.2.17.5.0';
$oid_c4_d2_tx = '.1.3.6.1.4.1.40989.10.16.4.2.18.4.0';
$oid_c4_d2_rx = '.1.3.6.1.4.1.40989.10.16.4.2.18.5.0';

// Discover Card 1 A1 TX Sensor
if (is_numeric($c1_a1_tx)) {
    $descr = 'Card 1 A1 Tx Power';
    $index = 'OAP-C1-OEO::vSFPA1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_a1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_a1_tx,
        'snmp'
    );
}

// Discover Card 1 A1 RX Sensor
if (is_numeric($c1_a1_rx)) {
    $descr = 'Card 1 A1 Rx Power';
    $index = 'OAP-C1-OEO::vSFPA1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_a1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_a1_rx,
        'snmp'
    );
}

// Discover Card 1 A2 TX Sensor
if (is_numeric($c1_a2_tx)) {
    $descr = 'Card 1 A2 Tx Power';
    $index = 'OAP-C1-OEO::vSFPA2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_a2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_a2_tx,
        'snmp'
    );
}

// Discover Card 1 A2 RX Sensor
if (is_numeric($c1_a2_rx)) {
    $descr = 'Card 1 A2 Rx Power';
    $index = 'OAP-C1-OEO::vSFPA2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_a2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_a2_rx,
        'snmp'
    );
}

// Discover Card 1 B1 TX Sensor
if (is_numeric($c1_b1_tx)) {
    $descr = 'Card 1 B1 Tx Power';
    $index = 'OAP-C1-OEO::vSFPB1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_b1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_b1_tx,
        'snmp'
    );
}

// Discover Card 1 B1 RX Sensor
if (is_numeric($c1_b1_rx)) {
    $descr = 'Card 1 B1 Rx Power';
    $index = 'OAP-C1-OEO::vSFPB1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_b1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_b1_rx,
        'snmp'
    );
}

// Discover Card 1 B2 TX Sensor
if (is_numeric($c1_b2_tx)) {
    $descr = 'Card 1 B2 Tx Power';
    $index = 'OAP-C1-OEO::vSFPB2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_b2_tx,
        'snmp'
    );
}

// Discover Card 1 B2 RX Sensor
if (is_numeric($c1_b2_rx)) {
    $descr = 'Card 1 B2 Rx Power';
    $index = 'OAP-C1-OEO::vSFPB2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_b2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_b2_rx,
        'snmp'
    );
}

// Discover Card 1 C1 TX Sensor
if (is_numeric($c1_c1_tx)) {
    $descr = 'Card 1 C1 Tx Power';
    $index = 'OAP-C1-OEO::vSFPC1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_c1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_c1_tx,
        'snmp'
    );
}

// Discover Card 1 C1 RX Sensor
if (is_numeric($c1_c1_rx)) {
    $descr = 'Card 1 C1 Rx Power';
    $index = 'OAP-C1-OEO::vSFPC1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_c1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_c1_rx,
        'snmp'
    );
}

// Discover Card 1 C2 TX Sensor
if (is_numeric($c1_c2_tx)) {
    $descr = 'Card 1 C2 Tx Power';
    $index = 'OAP-C1-OEO::vSFPC2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_c2_tx,
        'snmp'
    );
}

// Discover Card 1 C2 RX Sensor
if (is_numeric($c1_c2_rx)) {
    $descr = 'Card 1 C2 Rx Power';
    $index = 'OAP-C1-OEO::vSFPC2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_c2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_c2_rx,
        'snmp'
    );
}

// Discover Card 1 D1 TX Sensor
if (is_numeric($c1_d1_tx)) {
    $descr = 'Card 1 D1 Tx Power';
    $index = 'OAP-C1-OEO::vSFPD1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_d1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_d1_tx,
        'snmp'
    );
}

// Discover Card 1 D1 RX Sensor
if (is_numeric($c1_d1_rx)) {
    $descr = 'Card 1 D1 Rx Power';
    $index = 'OAP-C1-OEO::vSFPD1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_d1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_d1_rx,
        'snmp'
    );
}

// Discover Card 1 D2 TX Sensor
if (is_numeric($c1_d2_tx)) {
    $descr = 'Card 1 D2 Tx Power';
    $index = 'OAP-C1-OEO::vSFPD2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_d2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_d2_tx,
        'snmp'
    );
}

// Discover Card 1 D2 RX Sensor
if (is_numeric($c1_d2_rx)) {
    $descr = 'Card 1 D2 Rx Power';
    $index = 'OAP-C1-OEO::vSFPD2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c1_d2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c1_d2_rx,
        'snmp'
    );
}

// Discover Card 2 A1 TX Sensor
if (is_numeric($c2_a1_tx)) {
    $descr = 'Card 2 A1 Tx Power';
    $index = 'OAP-C2-OEO::vSFPA1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_a1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_a1_tx,
        'snmp'
    );
}

// Discover Card 2 A1 RX Sensor
if (is_numeric($c2_a1_rx)) {
    $descr = 'Card 2 A1 Rx Power';
    $index = 'OAP-C2-OEO::vSFPA1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_a1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_a1_rx,
        'snmp'
    );
}

// Discover Card 2 A2 TX Sensor
if (is_numeric($c2_a2_tx)) {
    $descr = 'Card 2 A2 Tx Power';
    $index = 'OAP-C2-OEO::vSFPA2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_a2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_a2_tx,
        'snmp'
    );
}

// Discover Card 2 A2 RX Sensor
if (is_numeric($c2_a2_rx)) {
    $descr = 'Card 2 A2 Rx Power';
    $index = 'OAP-C2-OEO::vSFPA2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_a2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_a2_rx,
        'snmp'
    );
}

// Discover Card 2 B1 TX Sensor
if (is_numeric($c2_b1_tx)) {
    $descr = 'Card 2 B1 Tx Power';
    $index = 'OAP-C2-OEO::vSFPB1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_b1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_b1_tx,
        'snmp'
    );
}

// Discover Card 2 B1 RX Sensor
if (is_numeric($c2_b1_rx)) {
    $descr = 'Card 2 B1 Rx Power';
    $index = 'OAP-C2-OEO::vSFPB1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_b1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_b1_rx,
        'snmp'
    );
}

// Discover Card 2 B2 TX Sensor
if (is_numeric($c2_b2_tx)) {
    $descr = 'Card 2 B2 Tx Power';
    $index = 'OAP-C2-OEO::vSFPB2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_b2_tx,
        'snmp'
    );
}

// Discover Card 2 B2 RX Sensor
if (is_numeric($c2_b2_rx)) {
    $descr = 'Card 2 B2 Rx Power';
    $index = 'OAP-C2-OEO::vSFPB2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_b2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_b2_rx,
        'snmp'
    );
}

// Discover Card 2 C1 TX Sensor
if (is_numeric($c2_c1_tx)) {
    $descr = 'Card 2 C1 Tx Power';
    $index = 'OAP-C2-OEO::vSFPC1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_c1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_c1_tx,
        'snmp'
    );
}

// Discover Card 2 C1 RX Sensor
if (is_numeric($c2_c1_rx)) {
    $descr = 'Card 2 C1 Rx Power';
    $index = 'OAP-C2-OEO::vSFPC1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_c1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_c1_rx,
        'snmp'
    );
}

// Discover Card 2 C2 TX Sensor
if (is_numeric($c2_c2_tx)) {
    $descr = 'Card 2 C2 Tx Power';
    $index = 'OAP-C2-OEO::vSFPC2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_c2_tx,
        'snmp'
    );
}

// Discover Card 2 C2 RX Sensor
if (is_numeric($c2_c2_rx)) {
    $descr = 'Card 2 C2 Rx Power';
    $index = 'OAP-C2-OEO::vSFPC2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_c2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_c2_rx,
        'snmp'
    );
}

// Discover Card 2 D1 TX Sensor
if (is_numeric($c2_d1_tx)) {
    $descr = 'Card 2 D1 Tx Power';
    $index = 'OAP-C2-OEO::vSFPD1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_d1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_d1_tx,
        'snmp'
    );
}

// Discover Card 2 D1 RX Sensor
if (is_numeric($c2_d1_rx)) {
    $descr = 'Card 2 D1 Rx Power';
    $index = 'OAP-C2-OEO::vSFPD1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_d1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_d1_rx,
        'snmp'
    );
}

// Discover Card 2 D2 TX Sensor
if (is_numeric($c2_d2_tx)) {
    $descr = 'Card 2 D2 Tx Power';
    $index = 'OAP-C2-OEO::vSFPD2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_d2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_d2_tx,
        'snmp'
    );
}

// Discover Card 2 D2 RX Sensor
if (is_numeric($c2_d2_rx)) {
    $descr = 'Card 2 D2 Rx Power';
    $index = 'OAP-C2-OEO::vSFPD2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c2_d2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c2_d2_rx,
        'snmp'
    );
}

// Discover Card 3 A1 TX Sensor
if (is_numeric($c3_a1_tx)) {
    $descr = 'Card 3 A1 Tx Power';
    $index = 'OAP-C3-OEO::vSFPA1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_a1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_a1_tx,
        'snmp'
    );
}

// Discover Card 3 A1 RX Sensor
if (is_numeric($c3_a1_rx)) {
    $descr = 'Card 3 A1 Rx Power';
    $index = 'OAP-C3-OEO::vSFPA1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_a1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_a1_rx,
        'snmp'
    );
}

// Discover Card 3 A2 TX Sensor
if (is_numeric($c3_a2_tx)) {
    $descr = 'Card 3 A2 Tx Power';
    $index = 'OAP-C3-OEO::vSFPA2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_a2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_a2_tx,
        'snmp'
    );
}

// Discover Card 3 A2 RX Sensor
if (is_numeric($c3_a2_rx)) {
    $descr = 'Card 3 A2 Rx Power';
    $index = 'OAP-C3-OEO::vSFPA2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_a2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_a2_rx,
        'snmp'
    );
}

// Discover Card 3 B1 TX Sensor
if (is_numeric($c3_b1_tx)) {
    $descr = 'Card 3 B1 Tx Power';
    $index = 'OAP-C3-OEO::vSFPB1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_b1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_b1_tx,
        'snmp'
    );
}

// Discover Card 3 B1 RX Sensor
if (is_numeric($c3_b1_rx)) {
    $descr = 'Card 3 B1 Rx Power';
    $index = 'OAP-C3-OEO::vSFPB1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_b1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_b1_rx,
        'snmp'
    );
}

// Discover Card 3 B2 TX Sensor
if (is_numeric($c3_b2_tx)) {
    $descr = 'Card 3 B2 Tx Power';
    $index = 'OAP-C3-OEO::vSFPB2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_b2_tx,
        'snmp'
    );
}

// Discover Card 3 B2 RX Sensor
if (is_numeric($c3_b2_rx)) {
    $descr = 'Card 3 B2 Rx Power';
    $index = 'OAP-C3-OEO::vSFPB2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_b2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_b2_rx,
        'snmp'
    );
}

// Discover Card 3 C1 TX Sensor
if (is_numeric($c3_c1_tx)) {
    $descr = 'Card 3 C1 Tx Power';
    $index = 'OAP-C3-OEO::vSFPC1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_c1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_c1_tx,
        'snmp'
    );
}

// Discover Card 3 C1 RX Sensor
if (is_numeric($c3_c1_rx)) {
    $descr = 'Card 3 C1 Rx Power';
    $index = 'OAP-C3-OEO::vSFPC1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_c1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_c1_rx,
        'snmp'
    );
}

// Discover Card 3 C2 TX Sensor
if (is_numeric($c3_c2_tx)) {
    $descr = 'Card 3 C2 Tx Power';
    $index = 'OAP-C3-OEO::vSFPC2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_c2_tx,
        'snmp'
    );
}

// Discover Card 3 C2 RX Sensor
if (is_numeric($c3_c2_rx)) {
    $descr = 'Card 3 C2 Rx Power';
    $index = 'OAP-C3-OEO::vSFPC2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_c2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_c2_rx,
        'snmp'
    );
}

// Discover Card 3 D1 TX Sensor
if (is_numeric($c3_d1_tx)) {
    $descr = 'Card 3 D1 Tx Power';
    $index = 'OAP-C3-OEO::vSFPD1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_d1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_d1_tx,
        'snmp'
    );
}

// Discover Card 3 D1 RX Sensor
if (is_numeric($c3_d1_rx)) {
    $descr = 'Card 3 D1 Rx Power';
    $index = 'OAP-C3-OEO::vSFPD1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_d1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_d1_rx,
        'snmp'
    );
}

// Discover Card 3 D2 TX Sensor
if (is_numeric($c3_d2_tx)) {
    $descr = 'Card 3 D2 Tx Power';
    $index = 'OAP-C3-OEO::vSFPD2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_d2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_d2_tx,
        'snmp'
    );
}

// Discover Card 3 D2 RX Sensor
if (is_numeric($c3_d2_rx)) {
    $descr = 'Card 3 D2 Rx Power';
    $index = 'OAP-C3-OEO::vSFPD2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c3_d2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c3_d2_rx,
        'snmp'
    );
}

// Discover Card 4 A1 TX Sensor
if (is_numeric($c4_a1_tx)) {
    $descr = 'Card 4 A1 Tx Power';
    $index = 'OAP-C4-OEO::vSFPA1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_a1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_a1_tx,
        'snmp'
    );
}

// Discover Card 4 A1 RX Sensor
if (is_numeric($c4_a1_rx)) {
    $descr = 'Card 4 A1 Rx Power';
    $index = 'OAP-C4-OEO::vSFPA1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_a1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_a1_rx,
        'snmp'
    );
}

// Discover Card 4 A2 TX Sensor
if (is_numeric($c4_a2_tx)) {
    $descr = 'Card 4 A2 Tx Power';
    $index = 'OAP-C4-OEO::vSFPA2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_a2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_a2_tx,
        'snmp'
    );
}

// Discover Card 4 A2 RX Sensor
if (is_numeric($c4_a2_rx)) {
    $descr = 'Card 4 A2 Rx Power';
    $index = 'OAP-C4-OEO::vSFPA2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_a2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_a2_rx,
        'snmp'
    );
}

// Discover Card 4 B1 TX Sensor
if (is_numeric($c4_b1_tx)) {
    $descr = 'Card 4 B1 Tx Power';
    $index = 'OAP-C4-OEO::vSFPB1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_b1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_b1_tx,
        'snmp'
    );
}

// Discover Card 4 B1 RX Sensor
if (is_numeric($c4_b1_rx)) {
    $descr = 'Card 4 B1 Rx Power';
    $index = 'OAP-C4-OEO::vSFPB1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_b1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_b1_rx,
        'snmp'
    );
}

// Discover Card 4 B2 TX Sensor
if (is_numeric($c4_b2_tx)) {
    $descr = 'Card 4 B2 Tx Power';
    $index = 'OAP-C4-OEO::vSFPB2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_b2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_b2_tx,
        'snmp'
    );
}

// Discover Card 4 B2 RX Sensor
if (is_numeric($c4_b2_rx)) {
    $descr = 'Card 4 B2 Rx Power';
    $index = 'OAP-C4-OEO::vSFPB2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_b2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_b2_rx,
        'snmp'
    );
}

// Discover Card 4 C1 TX Sensor
if (is_numeric($c4_c1_tx)) {
    $descr = 'Card 4 C1 Tx Power';
    $index = 'OAP-C4-OEO::vSFPC1TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_c1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_c1_tx,
        'snmp'
    );
}

// Discover Card 4 C1 RX Sensor
if (is_numeric($c4_c1_rx)) {
    $descr = 'Card 4 C1 Rx Power';
    $index = 'OAP-C4-OEO::vSFPC1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_c1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_c1_rx,
        'snmp'
    );
}

// Discover Card 4 C2 TX Sensor
if (is_numeric($c4_c2_tx)) {
    $descr = 'Card 4 C2 Tx Power';
    $index = 'OAP-C4-OEO::vSFPC2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_c2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_c2_tx,
        'snmp'
    );
}

// Discover Card 4 C2 RX Sensor
if (is_numeric($c4_c2_rx)) {
    $descr = 'Card 4 C2 Rx Power';
    $index = 'OAP-C4-OEO::vSFPC2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_c2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_c2_rx,
        'snmp'
    );
}

// Discover Card 4 D1 TX Sensor
if (is_numeric($c4_d1_tx)) {
    $descr = 'Card 4 D1 Tx Power';
    $index = 'OAP-C4-OEO::vSFPD1TxPower.0';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_d1_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_d1_tx,
        'snmp'
    );
}

// Discover Card 4 D1 RX Sensor
if (is_numeric($c4_d1_rx)) {
    $descr = 'Card 4 D1 Rx Power';
    $index = 'OAP-C4-OEO::vSFPD1RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_d1_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_d1_rx,
        'snmp'
    );
}

// Discover Card 4 D2 TX Sensor
if (is_numeric($c4_d2_tx)) {
    $descr = 'Card 4 D2 Tx Power';
    $index = 'OAP-C4-OEO::vSFPD2TxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_d2_tx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_d2_tx,
        'snmp'
    );
}

// Discover Card 4 D2 RX Sensor
if (is_numeric($c4_d2_rx)) {
    $descr = 'Card 4 D2 Rx Power';
    $index = 'OAP-C4-OEO::vSFPD2RxPower.0';
    $divisor = '100';
    $multiplier = '1';
    discover_sensor(
        null,
        'dbm',
        $device,
        $oid_c4_d2_rx,
        $index,
        'fs-nmu',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        $c4_d2_rx,
        'snmp'
    );
}
