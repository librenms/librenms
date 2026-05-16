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
 *
 * @copyright  2019 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Rutos2xx extends OS implements
    OSPolling,
    WirelessSnrDiscovery,
    WirelessRssiDiscovery
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $usage = SnmpQuery::numeric()->get([
            '.1.3.6.1.4.1.48690.2.11.0',
            '.1.3.6.1.4.1.48690.2.10.0',
            '.1.3.6.1.4.1.48690.2.2.1.25.1',
            '.1.3.6.1.4.1.48690.2.2.1.26.1',
        ])->values();

        $usage_sent = $this->firstNumeric($usage, ['.1.3.6.1.4.1.48690.2.11.0', '.1.3.6.1.4.1.48690.2.2.1.25.1']);
        $usage_received = $this->firstNumeric($usage, ['.1.3.6.1.4.1.48690.2.10.0', '.1.3.6.1.4.1.48690.2.2.1.26.1']);

        if ($usage_sent !== null && $usage_received !== null
            && $usage_sent >= 0 && $usage_received >= 0
        ) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('usage_sent', 'GAUGE', 0)
                ->addDataset('usage_received', 'GAUGE', 0);

            $fields = [
                'usage_sent' => $usage_sent,
                'usage_received' => $usage_received,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'rutos_2xx_mobileDataUsage', $tags, $fields);
            $this->enableGraph('rutos_2xx_mobileDataUsage');
        }
    }

    public function discoverWirelessSnr(): array
    {
        $oid = $this->resolveModemOid('.1.3.6.1.4.1.48690.2.22.0', '.1.3.6.1.4.1.48690.2.2.1.19.1');
        if ($oid === null) {
            return [];
        }

        return [
            new WirelessSensor(WirelessSensorType::Snr, $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'SINR', null, -1, 1),
        ];
    }

    public function discoverWirelessRssi(): array
    {
        $oid = $this->resolveModemOid('.1.3.6.1.4.1.48690.2.23.0', '.1.3.6.1.4.1.48690.2.2.1.20.1');
        if ($oid === null) {
            return [];
        }

        return [
            new WirelessSensor(WirelessSensorType::Rssi, $this->getDeviceId(), $oid, 'rutos-2xx', 1, 'RSRP', null, 1, 1),
        ];
    }

    private function firstNumeric(array $values, array $oids): ?float
    {
        foreach ($oids as $oid) {
            if (isset($values[$oid]) && is_numeric($values[$oid])) {
                return (float) $values[$oid];
            }
        }

        return null;
    }

    private function resolveModemOid(string $legacyOid, string $tableOid): ?string
    {
        $values = SnmpQuery::numeric()->get([$legacyOid, $tableOid])->values();

        foreach ([$legacyOid, $tableOid] as $oid) {
            if (isset($values[$oid]) && $values[$oid] !== '') {
                return $oid;
            }
        }

        return null;
    }
}
