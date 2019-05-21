<?php
/*
 * LibreNMS Moxa EtherDevice RAM discovery module
 *
 * Copyright (c) 2017 Aldemir Akpinar <aldemir.akpinar@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'moxa-etherdevice') {
    d_echo('Moxa EtherDevice');

    // Moxa people enjoy creating similar MIBs for each model!
    if ($device['sysDescr'] == 'IKS-6726A-2GTXSFP-T') {
        $mibmod = 'MOXA-IKS6726A-MIB';
    } elseif ($device['sysDescr'] == 'EDS-G508E-T') {
        $mibmod = 'MOXA-EDSG508E-MIB';
    } elseif ($device['sysDescr'] == 'EDS-P510A-8PoE-2GTXSFP-T') {
        $mibmod = 'MOXA-EDSP510A8POE-MIB';
    } elseif ($device['sysDescr'] == 'EDS-G512E-8PoE-T') {
        $mibmod = 'MOXA-EDSG512E8POE-MIB';
    }

    $mem_res = snmp_get_multi_oid($device, ['totalMemory.0', 'freeMemory.0'], '-OQUs', $mibmod);
    $total = $mem_res['totalMemory.0'];
    $avail = $mem_res['freeMemory.0'];

    if ((is_numeric($total)) && (is_numeric($avail))) {
        discover_mempool($valid_mempool, $device, 0, 'moxa-etherdevice-mem', 'Memory', '1', null, null);
    }
}
