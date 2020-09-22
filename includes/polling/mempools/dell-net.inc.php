<?php
/*
*  * LibreNMS DELL net memory information module
*   *
*    * Copyright (c) 2019 Erik van Helden
*     * This program is free software: you can redistribute it and/or modify it
*      * under the terms of the GNU General Public License as published by the
*       * Free Software Foundation, either version 3 of the License, or (at your
*        * option) any later version.  Please see LICENSE.txt at the top level of
*         * the source code distribution for details.
*          */

$total = snmp_get($device, '.1.3.6.1.4.1.6027.3.26.1.4.3.1.6.2.1.1', '-OvQ');
$usage = snmp_get($device, '.1.3.6.1.4.1.6027.3.26.1.4.4.1.6.2.1.1', '-OvQ');
$usage_perc = $usage / 100;
$used = $total * $usage_perc;
$free = $total - $used;

$mempool['total'] = $total * 1024 * 1024;
$mempool['free'] = $free * 1024 * 1024;
$mempool['used'] = $used * 1024 * 1024;
