<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


echo 'Moxa EtherDevice CPU Usage';

if ($device['os'] == 'moxa-etherdevice') {
    $usage = str_replace('"', "", snmp_get($device, 'MOXA-IKS6726A-MIB::cpuLoading30s.0', '-OvQ'));
    //$proc = $usage;

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
