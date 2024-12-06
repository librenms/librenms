<?php
/**
 * IcrOs.php
 *
 * Advantech ICR-OS
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
 * @copyright  2022 Mathias Bøhn Grytemark
 * @author     Mathias Bøhn Grytemark <mathias@grytemark.no>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\OS;

class IcrOs extends OS implements
    WirelessRssiDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessSinrDiscovery
{
    private function runWirelessSensor(string $miboid, string $what, array $nums): array
    {
        $sensors = [];
        foreach ($nums as $index => $num) {
            $oid = "$miboid.$num.0";
            $mobile = $index + 1;
            $name = "Mobile $mobile $what";
            $sensors[] = new WirelessSensor(
                strtolower($what),
                $this->getDeviceId(),
                $oid,
                'icr-os',
                $index,
                $name
            );
        }

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $miboid = '.1.3.6.1.4.1.30140.4';
        $what = 'RSSI';
        $nums = [30, 130];

        return $this->runWirelessSensor($miboid, $what, $nums);
    }

    public function discoverWirelessRsrp()
    {
        $miboid = '.1.3.6.1.4.1.30140.4';
        $what = 'RSRP';
        $nums = [32, 132];

        return $this->runWirelessSensor($miboid, $what, $nums);
    }

    public function discoverWirelessRsrq()
    {
        $miboid = '.1.3.6.1.4.1.30140.4';
        $what = 'RSRQ';
        $nums = [33, 133];

        return $this->runWirelessSensor($miboid, $what, $nums);
    }

    public function discoverWirelessSinr()
    {
        $miboid = '.1.3.6.1.4.1.30140.4';
        $what = 'SINR';
        $nums = [41, 141];

        return $this->runWirelessSensor($miboid, $what, $nums);
    }
}
