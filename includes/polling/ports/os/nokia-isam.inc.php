<?php
/**
 * nokia-isam.inc.php
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
 * @copyright  2019 Vitali Kari
 * @copyright  2024 Rinse Kloek
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Rinse Kloek <rinse@kindes.nl>
 */

// Use proprietary asamIfExtCustomerId as ifAlias for Nokia ISAM Plattform. The default IF-MIB fields are here quite meaningless
$isam_port_stats = snmpwalk_cache_oid($device, 'asamIfExtCustomerId', [], 'ITF-MIB-EXT', 'nokia-isam');
foreach ($isam_port_stats as $index => $value) {
    $port_stats[$index]['ifAlias'] = $isam_port_stats[$index]['asamIfExtCustomerId'];
}

// Now do the same as in ports.inc full ports
SnmpQuery::context('ihub')->hideMib()->walk(['IF-MIB::ifXEntry'])->table(1, $port_stats);
$hc_test = array_slice($port_stats, 0, 1);

// If the device doesn't have ifXentry data, fetch ifEntry instead.
if (! is_numeric($hc_test[0]['ifHCInOctets'] ?? null) || ! is_numeric($hc_test[0]['ifHighSpeed'] ?? null)) {
    $ifEntrySnmpFlags = ['-OQUst'];
    SnmpQuery::options($ifEntrySnmpFlags)->context('ihub')->hideMib()->walk(['IF-MIB::ifEntry'])->table(1, $port_stats);
} else {
    // For devices with ifXentry data, only specific ifEntry keys are fetched to reduce SNMP load
    foreach ($ifmib_oids as $oid) {
        echo "$oid ";
        SnmpQuery::options('-OQUst')->context('ihub')->hideMib()->walk(['IF-MIB::' . $oid])->table(1, $port_stats);
    }
}

unset($isam_ports_stats);
