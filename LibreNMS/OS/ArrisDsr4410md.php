<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class ArrisDsr4410md extends OS implements
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessQualityDiscovery
{
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.11.9.0';

        return [
            new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                $oid,
                'arris-dsr4410md',
                0,
                'Receive Signal Level',
                null,
                null,
                10
            ),
        ];
    }

    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.16.6.8.0';

        return [
            new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $oid,
                'arris-dsr4410md',
                0,
                'Receive SNR',
                null,
                null,
                10
            ),
        ];
    }

    public function discoverWirelessQuality()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.11.8.0';

        return [
            new WirelessSensor(
                'quality',
                $this->getDeviceId(),
                $oid,
                'arris-dsr4410md',
                0,
                'Receive Quality'
            ),
        ];
    }
}
