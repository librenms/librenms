<?php
/**
 * RutosRutx.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class RutosRutx extends OS implements
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessSinrDiscovery,
    WirelessCellDiscovery
{
    public function discoverWirelessRsrp()
    {
        $oid = '.1.3.6.1.4.1.48690.2.2.1.20.1'; // TELTONIKA-RUTX-MIB::SINR.1

        return [
            new WirelessSensor('rsrp', $this->getDeviceId(), $oid, 'rutos-rutx', 1, 'RSRP', null, -1, 1),
        ];
    }

    public function discoverWirelessRsrq()
    {
        $oid = '.1.3.6.1.4.1.48690.2.2.1.21.1'; // TELTONIKA-RUTX-MIB::RSRP.1

        return [
            new WirelessSensor('rsrq', $this->getDeviceId(), $oid, 'rutos-rutx', 1, 'RSRQ', null, 1, 1),
        ];
    }

    public function discoverWirelessSinr()
    {
        $oid = '.1.3.6.1.4.1.48690.2.2.1.19.1'; // TELTONIKA-RUTX-MIB::RSRP.1

        return [
            new WirelessSensor('sinr', $this->getDeviceId(), $oid, 'rutos-rutx', 1, 'SINR', null, 1, 1),
        ];
    }

    public function discoverWirelessCell()
    {
        $oid = '.1.3.6.1.4.1.48690.2.2.1.18.1'; // TELTONIKA-RUTX-MIB::CELLID.1

        return [
            new WirelessSensor('cell', $this->getDeviceId(), $oid, 'rutos-rutx', 1, 'CELL ID', null, 1, 1),
        ];
    }
}
