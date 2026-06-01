<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Albentiaaos extends OS implements
    WirelessClientsDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery
{
    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessClients()
    {
        return [
            new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.28087.12.10.2.1.0',
                'albentiaaos',
                0,
                'Registered users'
            ),
        ];
    }

    public function encodeStringIndex(string $idx): string
    {
        $out = (string) strlen($idx);
        foreach (str_split($idx) as $ch) {
            $out .= '.' . ord($ch);
        }

        return $out;
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessFrequency()
    {
        $sensors = [];
        foreach (SnmpQuery::cache()->walk('ALBENTIA-AS-MIB::radioInfoTable')->table(1) as $idx => $row) {
            $freq = $row['ALBENTIA-AS-MIB::radioInfoFreq'] ?? null;
            if ($freq === null) {
                continue;
            }
            $color = (string) $idx;
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.28087.12.10.10.5.1.13.' . $this->encodeStringIndex($color),
                'albentiaaos',
                $color,
                'Sector ' . $color,
                (int) $freq
            );
        }

        return $sensors;
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessDistance()
    {
        foreach (SnmpQuery::cache()->walk('ALBENTIA-AS-MIB::radioInfoTable')->table(1) as $idx => $row) {
            $dist = $row['ALBENTIA-AS-MIB::radioInfoDistance'] ?? null;
            if ($dist === null) {
                continue;
            }

            return [
                new WirelessSensor(
                    WirelessSensorType::Distance,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.28087.12.10.10.5.1.12.' . $this->encodeStringIndex((string) $idx),
                    'albentiaaos',
                    0,
                    'Distance',
                    (int) $dist,
                    1,
                    1000
                ),
            ];
        }

        return [];
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessPower()
    {
        foreach (SnmpQuery::cache()->walk('ALBENTIA-AS-MIB::radioInfoTable')->table(1) as $idx => $row) {
            $tx = $row['ALBENTIA-AS-MIB::radioInfoTxPow'] ?? null;
            if ($tx === null) {
                continue;
            }

            return [
                new WirelessSensor(
                    WirelessSensorType::Power,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.28087.12.10.10.5.1.6.' . $this->encodeStringIndex((string) $idx),
                    'albentiaaos',
                    0,
                    'TxPow',
                    (int) $tx
                ),
            ];
        }

        return [];
    }
}
