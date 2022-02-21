<?php
/*
 * LibreNMS load sensor for PrimeKey Hardware Appliances
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * The OIDs described here, for the EJBCA Appliance:
 * https://doc.primekey.com/ejbca-appliance/operations/webconf-configurator-of-hardware-appliance/monitoring
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2022 LibreNMS
 * @author     LibreNMS Contributors
 */

$oids = [
    50 => [
        'descr' => ' 1min average',
        'oid'   => '.1.3.6.1.4.1.22408.1.1.2.1.5.108.111.97.100.50.1',
        'group' => 'CPU',
    ],
    51 => [
        'descr' => ' 5min average',
        'oid'   => '.1.3.6.1.4.1.22408.1.1.2.1.5.108.111.97.100.51.1',
        'group' => 'CPU',
    ],
    52 => [
        'descr' => '15min average',
        'oid'   => '.1.3.6.1.4.1.22408.1.1.2.1.5.108.111.97.100.52.1',
        'group' => 'CPU',
    ],
];

$class = 'load';

$type = 'primekey';
$divisor = 1;
$multiplier = 1;
$low_limit = null;
$low_warn_limit = null;
$warn_limit = null;
$high_limit = 3;  // Say 4 core processor e.g. Intel(R) Xeon(R) CPU E3-1225 v5
$poller_type = 'snmp';
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;

$transaction = snmp_get_multi_oid($device, array_column($oids, 'oid'));

foreach ($oids as $index => $entry) {
    $oid = $entry['oid'];
    $descr = $entry['descr'];
    $group = $entry['group'];

    if (! empty($transaction)) {
        $current = $transaction[$oid];

        if (is_numeric($current)) {
            discover_sensor($valid['sensor'],
                            $class,
                            $device,
                            $oid,
                            $index,
                            $type,
                            $descr,
                            $divisor,
                            $multiplier,
                            $low_limit,
                            $low_warn_limit,
                            $warn_limit,
                            $high_limit,
                            $current,
                            $poller_type,
                            $entPhysicalIndex,
                            $entPhysicalIndex_measured,
                            $user_func,
                            $group
                            );
        }
    }
}
unset($transaction, $class, $oid, $index, $type, $descr, $divisor,
       $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit,
       $current, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured,
       $user_func, $group);
