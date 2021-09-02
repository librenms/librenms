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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Rutos2xx extends OS implements
    OSPolling,
    WirelessSnrDiscovery,
    WirelessRssiDiscovery
{
    public function pollOS()
    {
        // Mobile Data Usage
        $usage = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.48690.2.11.0',
            '.1.3.6.1.4.1.48690.2.10.0',
        ]);

        $usage_sent = $usage['.1.3.6.1.4.1.48690.2.11.0'];
        $usage_received = $usage['.1.3.6.1.4.1.48690.2.10.0'];

        if ($usage_sent >= 0 && $usage_received >= 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('usage_sent', 'GAUGE', 0)
                ->addDataset('usage_received', 'GAUGE', 0);

            $fields = [
                'usage_sent' => $usage_sent,
                'usage_received' => $usage_received,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'rutos_2xx_mobileDataUsage', $tags, $fields);
            $this->enableGraph('rutos_2xx_mobileDataUsage');
        }
    }

    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.48690.2.22.0'; // TELTONIKA-MIB::SINR.0

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'SINR', null, -1, 1),
        ];
    }

    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.48690.2.23.0'; // TELTONIKA-MIB::RSRP.0

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'RSRP', null, 1, 1),
        ];
    }
}
