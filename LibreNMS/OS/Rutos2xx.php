<?php
/**
 * Rutos2xx.php
 *
 * -Description-
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
 * @copyright  2019 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\OS;

class Rutos2xx extends OS implements
    WirelessSinrDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery
{
    public function discoverWirelessSinr()
    {
        $oid = '.1.3.6.1.4.1.48690.2.22.0'; // TELTONIKA-MIB::SINR.0
        return array(
            new WirelessSensor('sinr', $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'SINR', null, -1, 1),
        );
    }

    public function discoverWirelessRsrp()
    {
        $oid = '.1.3.6.1.4.1.48690.2.23.0'; // TELTONIKA-MIB::RSRP.0
        return array(
            new WirelessSensor('rsrp', $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'RSRP', null, 1, 1),
        );
    }
    
    public function discoverWirelessRsrq()
    {
        $oid = '.1.3.6.1.4.1.48690.2.24.0'; // TELTONIKA-MIB::RSRQ.0
        return array(
            new WirelessSensor('rsrq', $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'RSRQ', null, 1, 1),
        );
    }
}
