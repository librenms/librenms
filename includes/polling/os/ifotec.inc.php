<?php
/**
 * ifotec.inc.php
 *
 * LibreNMS os poller module for Ifotec devices
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
 * @author     Cedric MARMONIER
 */

//echo "#### Call polling ifotec.inc.php #########################################################\n";

if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.21362.100.')) {
    //echo " ifotec.inc.php : get version " . $device['sysObjectID'] . "\n";

    $ifoSysProductIndex = snmp_get($device, '.1.3.6.1.4.1.21362.101.1.1.0', '-Oqv');
    if($ifoSysProductIndex != NULL){

        $ifoSysSoftware   = snmp_get($device, '.1.3.6.1.4.1.21362.101.1.2.1.7.' . $ifoSysProductIndex, '-Oqv');
        $ifoSysBootloader = snmp_get($device, '.1.3.6.1.4.1.21362.101.1.2.1.8.' . $ifoSysProductIndex, '-Oqv');
        $version = $ifoSysSoftware . " (Bootloader " . $ifoSysBootloader . ")";


        $serial   = snmp_get($device, '.1.3.6.1.4.1.21362.101.1.2.1.5.' . $ifoSysProductIndex, '-Oqv');

        
    } else {
        //echo "  ifotec.inc.php : ifoSysProductIndex NONE\n";
    } 

} else {
    $ifoSysProductIndex = 0;
}


// sysDecr = (<product_reference> . ' : ' . <product_description>) OR (<product_reference>)
list($hardware) = explode(' : ', $device['sysDescr'], 2);
$hardware = $hardware;

//echo "#### END polling ifotec.inc.php #########################################################\n\n";