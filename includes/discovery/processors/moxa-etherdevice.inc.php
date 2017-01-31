<?php
/*
 * LibreNMS Moxa Etherdevice Processor Discovery module
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
    echo 'Moxa EtherDevice Switch : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'cpuLoading30s.0', '-Ovq', 'MOXA-IKS6726A-MIB');
    d_echo($usage."\n");
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'cpuLoading30s.0', '0', 'moxa-etherdevice', $descr, '1', $usage, null, null);
    }
}
