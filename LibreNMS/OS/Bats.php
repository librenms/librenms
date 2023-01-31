<?php
/**
 * Bats.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Location;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Bats extends OS implements
    OSDiscovery,
    WirelessSnrDiscovery,
    WirelessRssiDiscovery
{
    public function fetchLocation(): Location
    {
        $location = parent::fetchLocation();
        $lat = snmp_get($this->getDeviceArray(), 'AATS-MIB::networkGPSLatitudeFloat.0', '-Oqv');
        $lng = snmp_get($this->getDeviceArray(), 'AATS-MIB::networkGPSLongitudeFloat.0', '-Oqv');
        $pointing = snmp_get($this->getDeviceArray(), 'AATS-MIB::status.0', '-Oqv');

        return new Location([
            'location' => 'At ' . (string) $lat . ', ' . (string) $lng . '. ' . $pointing,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.37069.1.2.5.3.0';

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'bats', 0, 'SNR'),
        ];
    }

    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.37069.1.2.4.3.0';

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'bats', 0, 'RSSI'),
        ];
    }
}
