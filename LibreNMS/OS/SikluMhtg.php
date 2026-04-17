<?php

/**
 * SikluMhtg.php
 *
 * Wireless sensor discovery for Siklu MultiHaul Terragraph (MH-TG) radios.
 *
 * Reads the rbTgRcLinksActiveTable from RADIO-BRIDGE-MH-TG-MIB. One row per
 * active RF link, indexed by ifIndex. Exposes RSSI, SNR, rate (both
 * directions), and packet error ratio (both directions) as wireless sensors.
 *
 * @link https://www.librenms.org
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRatioDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class SikluMhtg extends OS implements
    WirelessErrorRatioDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{
    private const TABLE_BASE = '.1.3.6.1.4.1.31926.35.1.1.3.1.1';

    /**
     * @return array<int,string> ifIndex => remote node assigned name
     */
    private function getRemoteNames(): array
    {
        $walk = snmpwalk_cache_oid(
            $this->getDeviceArray(),
            'rbTgRcLinksActiveRemoteName',
            [],
            'RADIO-BRIDGE-MH-TG-MIB'
        );

        $result = [];
        foreach ($walk as $ifIndex => $row) {
            $result[$ifIndex] = $row['rbTgRcLinksActiveRemoteName'] ?? "link-$ifIndex";
        }
        return $result;
    }

    public function discoverWirelessRssi()
    {
        $sensors = [];
        foreach ($this->getRemoteNames() as $ifIndex => $name) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                self::TABLE_BASE . ".7.$ifIndex",
                'siklu-mhtg',
                $ifIndex,
                "RSSI ($name)"
            );
        }
        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $sensors = [];
        foreach ($this->getRemoteNames() as $ifIndex => $name) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Snr,
                $this->getDeviceId(),
                self::TABLE_BASE . ".8.$ifIndex",
                'siklu-mhtg',
                $ifIndex,
                "SNR ($name)"
            );
        }
        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $sensors = [];
        foreach ($this->getRemoteNames() as $ifIndex => $name) {
            // MIB reports Mbps, LibreNMS stores rate in bps.
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                self::TABLE_BASE . ".14.$ifIndex",
                'siklu-mhtg',
                "rx.$ifIndex",
                "RX Rate ($name)",
                null,
                1000000,
                1
            );
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                self::TABLE_BASE . ".15.$ifIndex",
                'siklu-mhtg',
                "tx.$ifIndex",
                "TX Rate ($name)",
                null,
                1000000,
                1
            );
        }
        return $sensors;
    }

    public function discoverWirelessErrorRatio()
    {
        $sensors = [];
        foreach ($this->getRemoteNames() as $ifIndex => $name) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::ErrorRatio,
                $this->getDeviceId(),
                self::TABLE_BASE . ".11.$ifIndex",
                'siklu-mhtg',
                "rx.$ifIndex",
                "RX PER ($name)"
            );
            $sensors[] = new WirelessSensor(
                WirelessSensorType::ErrorRatio,
                $this->getDeviceId(),
                self::TABLE_BASE . ".12.$ifIndex",
                'siklu-mhtg',
                "tx.$ifIndex",
                "TX PER ($name)"
            );
        }
        return $sensors;
    }
}