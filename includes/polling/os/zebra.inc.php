<?php
/**
 * zebra.inc.php
 *
 * Detect print server information
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// ESI-MIB::genProductNumber.0 .1.3.6.1.4.1.683.1.4.0
// ESI-MIB::genSerialNumber.0 .1.3.6.1.4.1.683.1.5.0
// ESI-MIB::genVersion.0 .1.3.6.1.4.1.683.1.9.0
use Illuminate\Support\Str;

if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.683')) {
    $oids = [
        'hardware' => '.1.3.6.1.4.1.683.1.4.0',
        'serial' => '.1.3.6.1.4.1.683.1.5.0',
        'version' => '.1.3.6.1.4.1.683.1.9.0',
    ];
    $os_data = snmp_get_multi_oid($device, $oids);
    foreach ($oids as $var => $oid) {
        $$var = trim($os_data[$oid], '"');
    }
}

if (Str::contains($device['sysDescr'], 'Wireless')) {
    $features = 'wireless';
} else {
    $features = 'wired';
}
