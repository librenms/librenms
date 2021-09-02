<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

echo 'EATON-ATS';

$oids = snmpwalk_cache_oid($device, 'ats2InputVoltage', [], 'EATON-ATS2-MIB');
foreach ($oids as $volt_id => $data) {
    //we need to retrieve the numerical value of oid
    $source_oid = 'EATON-ATS2-MIB::ats2InputIndex.' . $volt_id;
    $num_id = snmp_get($device, $source_oid, '-Oqve');
    $volt_oid = '.1.3.6.1.4.1.534.10.2.2.2.1.2.' . $num_id;
    $index = '.1.3.6.1.4.1.534.10.2.2.2.1.1.' . $num_id;
    $descr = 'Input';
    if (count($oids) > 1) {
        $source = snmp_get($device, $source_oid, '-Oqv');
        $descr .= " $source";
    }
    $type = 'eaton-ats';
    $divisor = 10;
    $current = $data['ats2InputVoltage'] / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
