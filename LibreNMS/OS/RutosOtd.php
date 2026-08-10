<?php

/**
 * RutosOtd.php
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
 * @copyright  2025 Glenn Mattheij
 * @author     Glenn Mattheij
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\OS;

class RutosOtd extends OS implements
    WirelessRssiDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessSinrDiscovery,
    WirelessCellDiscovery
{
    /**
     * @return array<WirelessSensor>
     */
    public function discoverWirelessRssi(): array
    {
        return $this->buildModemSensors(WirelessSensorType::Rssi, '12', 'RSSI', 'mSignal');
    }

    /**
     * @return array<WirelessSensor>
     */
    public function discoverWirelessRsrp(): array
    {
        return $this->buildModemSensors(WirelessSensorType::Rsrp, '20', 'RSRP', 'mRSRP');
    }

    /**
     * @return array<WirelessSensor>
     */
    public function discoverWirelessRsrq(): array
    {
        return $this->buildModemSensors(WirelessSensorType::Rsrq, '21', 'RSRQ', 'mRSRQ');
    }

    /**
     * @return array<WirelessSensor>
     */
    public function discoverWirelessSinr(): array
    {
        return $this->buildModemSensors(WirelessSensorType::Sinr, '19', 'SINR', 'mSINR');
    }

    /**
     * @return array<WirelessSensor>
     */
    public function discoverWirelessCell(): array
    {
        return $this->buildModemSensors(WirelessSensorType::Cell, '18', 'CELL ID', 'mCellID');
    }

    /**
     * @return array<WirelessSensor>
     */
    private function buildModemSensors(WirelessSensorType $type, string $oidLeaf, string $label, string $field): array
    {
        $sensors = [];
        foreach ($this->getCacheTable('TELTONIKA-OTD-MIB::modemTable') as $index => $entry) {
            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.' . $oidLeaf . '.' . $index,
                'rutos-otd',
                $index,
                'Modem ' . ($entry['mIndex'] ?? null) . ' ' . $label,
                $entry[$field] ?? null
            );
        }

        return $sensors;
    }
}
