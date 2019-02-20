<?php
/*
 * LibreNMS Dantel Webmon poller module
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Mike Williams
 * @author     Mike Williams <mike@mgww.net>
 */

$version = snmp_get($device, '.1.3.6.1.4.1.994.3.4.7.1.82.0.0', '-OQv', 'WEBMON-EDGE-MATRIX-MIB');
