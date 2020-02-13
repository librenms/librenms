<?php
/**
 * bridge.inc.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  LibreNMS contributors
 * @author     JoseUPV
 */

// Try nokia/aos7/ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement first
$dot1d = snmpwalk_group($device, 'slMacAddressGblManagement', 'ALCATEL-IND1-MAC-ADDRESS-MIB',0,array(),'nokia/aos7');
if (!empty($dot1d)) {
    echo 'AOS7+ MAC-ADDRESS-MIB:';
    $fdbPort_table=array();
    foreach ($dot1d['slMacAddressGblManagement'] as $slMacDomain => $data)
        foreach ($data as $slLocaleType => $data2)
            foreach($data2 as $portLocal => $data3)
                foreach($data3 as $vlanLocal => $data4)
                    $fdbPort_table[$vlanLocal]=array('dot1qTpFdbPort' => array_combine(array_keys($data4[0]),array_fill(0,count($data4[0]),$portLocal)));
} else {
    // nokia/ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition
    $dot1d = snmpwalk_group($device, 'slMacAddressDisposition', 'ALCATEL-IND1-MAC-ADDRESS-MIB', 0,array(),'nokia');
    if (!empty($dot1d)) {
        echo 'AOS6 MAC-ADDRESS-MIB: ';
        $fdbPort_table=array();
        foreach ($dot1d['slMacAddressDisposition'] as $portLocal => $data)
            foreach ($data as $vlanLocal => $data2)
		$fdbPort_table[$vlanLocal]=array('dot1qTpFdbPort' => array_combine(array_keys($data2),array_fill(0,count($data2),$portLocal)));
    }
}
