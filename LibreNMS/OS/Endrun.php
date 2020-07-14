<?php
/**
 * EndRun.php
 *
 * EndRun Tempus LX NTP devices
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
 * @copyright  2020 Hans Erasmus
 * @author     Hans Erasmus <erasmushans27@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Endrun extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $info = snmp_getnext_multi($this->getDevice(), 'cntpVersion cdmaVersion', '-OQUs', 'TEMPUSLXUNISON-MIB');
        $info2 = snmp_getnext_multi($this->getDevice(), 'snmpSetSerialNo', '-OQUs', 'SNMPv2-MIB');
        $device->serial = $info2['snmpSetSerialNo'];
	$device->features = $info['cdmaVersion'];
        // The cntpVersion string output is rather long. Ex. Tempus LX CDMA 6010-0042-000 v 5.70 - Wed Oct 1 04:39:21 UTC 2014
	// so we use preg_split to get the values we wanted.
	$Descr_chopper = preg_split('/v /', $info['cntpVersion']);
	$operating_system = preg_split('/ - /', $Descr_chopper[1]);
	$device->version = $operating_system[0];
	$device->hardware = $Descr_chopper[0];
    }
}
