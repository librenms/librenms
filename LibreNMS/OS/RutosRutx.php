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
 * @author     H. DAY
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
use SnmpQuery;

class RutosRutx extends OS implements
    WirelessRssiDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessSinrDiscovery,
    WirelessCellDiscovery
{
    public function discoverWirelessRssi(): array
    {
        $data = SnmpQuery::cache()->walk('TELTONIKA-RUTX-MIB::modemTable')->table(1);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.12.' . $index,
                'rutos-rutx',
                $index,
                'Modem ' . ($entry['TELTONIKA-RUTX-MIB::mIndex'] ?? null) . ' RSSI',
                $entry['TELTONIKA-RUTX-MIB::mSignal']
            );
        }

        return $sensors;
    }

    public function discoverWirelessRsrp(): array
    {
        $data = SnmpQuery::cache()->walk('TELTONIKA-RUTX-MIB::modemTable')->table(1);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rsrp,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.20.' . $index,
                'rutos-rutx',
                $index,
                'Modem ' . ($entry['TELTONIKA-RUTX-MIB::mIndex'] ?? null) . ' RSRP',
                $entry['TELTONIKA-RUTX-MIB::mRSRP']
            );
        }

        return $sensors;
    }

    public function discoverWirelessRsrq(): array
    {
        $data = SnmpQuery::cache()->walk('TELTONIKA-RUTX-MIB::modemTable')->table(1);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rsrq,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.21.' . $index,
                'rutos-rutx',
                $index,
                'Modem ' . ($entry['TELTONIKA-RUTX-MIB::mIndex'] ?? null) . ' RSRQ',
                $entry['TELTONIKA-RUTX-MIB::mRSRQ']
            );
        }

        return $sensors;
    }

    public function discoverWirelessSinr(): array
    {
        $data = SnmpQuery::cache()->walk('TELTONIKA-RUTX-MIB::modemTable')->table(1);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Sinr,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.19.' . $index,
                'rutos-rutx',
                $index,
                'Modem ' . ($entry['TELTONIKA-RUTX-MIB::mIndex'] ?? null) . ' SINR',
                $entry['TELTONIKA-RUTX-MIB::mSINR']
            );
        }

        return $sensors;
    }

    public function discoverWirelessCell(): array
    {
        $data = SnmpQuery::cache()->walk('TELTONIKA-RUTX-MIB::modemTable')->table(1);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Cell,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.48690.2.2.1.18.' . $index,
                'rutos-rutx',
                $index,
                'Modem ' . ($entry['TELTONIKA-RUTX-MIB::mIndex'] ?? null) . ' CELL ID',
                $entry['TELTONIKA-RUTX-MIB::mCellID'] ?? null
            );
        }

        return $sensors;
    }
}
