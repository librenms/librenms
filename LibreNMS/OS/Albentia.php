<?php

//
// LibreNMS\OS\Albentia
//
// Wireless-class sensors for Albentia BS, so the values land in the
// Wireless > Frequency / Clients / Distance / Power / RSSI dashboards
// instead of the regular Sensors page.
//
// Per-sector frequency creates one Wireless::Frequency sensor per radio
// (8 sectors on AXS-BS-850-N, 4 on AXS-BS-452-N).
// Distance / TxPow / TargetRSSI are configured globally on Albentia BS
// (all sectors return the same value) so we publish a single sensor
// each, indexed 0.

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\OS;

class Albentia extends OS implements
    WirelessClientsDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery
{
    /** @var array<string, array<string, string|int>>|null */
    private ?array $radioInfoCache = null;

    /**
     * @return array<string, array<string, string|int>>
     */
    private function getRadioInfo(): array
    {
        if ($this->radioInfoCache === null) {
            $this->radioInfoCache = (array) snmpwalk_cache_oid(
                $this->getDeviceArray(),
                'radioInfoTable',
                [],
                'ALBENTIA-AS-MIB',
                'albentia',
                '-OteQUsb'
            );
        }

        return $this->radioInfoCache;
    }

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
                'albentia',
                0,
                'Registered users'
            ),
        ];
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessFrequency()
    {
        $sensors = [];
        foreach ($this->getRadioInfo() as $idx_enc => $row) {
            $color = (string) ($row['radioInfoSectorColor'] ?? $idx_enc);
            $freq = $row['radioInfoFreq'] ?? null;
            if ($freq === null) {
                continue;
            }
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.28087.12.10.10.5.1.13.' . $idx_enc,
                'albentia',
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
        foreach ($this->getRadioInfo() as $idx_enc => $row) {
            $dist = $row['radioInfoDistance'] ?? null;
            if ($dist === null) {
                continue;
            }

            return [
                new WirelessSensor(
                    WirelessSensorType::Distance,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.28087.12.10.10.5.1.12.' . $idx_enc,
                    'albentia',
                    0,
                    'Distance',
                    (int) $dist,
                    1,    // multiplier
                    1000  // divisor: MIB is metres, Wireless > Distance expects km
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
        foreach ($this->getRadioInfo() as $idx_enc => $row) {
            $tx = $row['radioInfoTxPow'] ?? null;
            if ($tx === null) {
                continue;
            }

            return [
                new WirelessSensor(
                    WirelessSensorType::Power,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.28087.12.10.10.5.1.6.' . $idx_enc,
                    'albentia',
                    0,
                    'TxPow',
                    (int) $tx
                ),
            ];
        }

        return [];
    }
}
