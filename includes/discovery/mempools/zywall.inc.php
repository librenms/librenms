<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'zywall') {
    d_echo('Zywall');
    $usage = snmp_get($device, '.1.3.6.1.4.1.890.1.6.22.1.2.0', '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'zywall', 'Memory', '1', null, null);
    }
    
    $flash = snmp_get($device, '.1.3.6.1.4.1.890.1.15.3.2.6.0', '-Ovq');
    if (is_numeric($flash)) {
        discover_mempool($valid_mempool, $device, '1', 'zywall', 'Flash', '1', null, null);
    }
}
