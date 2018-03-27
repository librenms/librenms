<?php
/**
 * Lastmile.php
 *
 * Last Mile Gear CTM
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
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */
namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\OS;

class Lastmile extends OS implements WirelessClientsDiscovery
{
    /**
     * This is a bit of a stretch. Clients are referencing `satellites`
     * as this is a gps timing device.
     */
    public function discoverWirelessClients()
    {
        $oid =  '.1.3.6.1.4.1.25868.1.5.0';
        return array(
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'lastmile', 0, 'Satellites in View', null)
        );
    }
}
