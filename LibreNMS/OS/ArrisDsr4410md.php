<?php
namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\OS;

class ArrisDsr4410md extends OS implements
    OSDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessQualityDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $arrisdsr_data = snmp_get_multi_oid($this->getDevice(), ['.1.3.6.1.4.1.1166.1.621.9.2.3.0', '.1.3.6.1.4.1.1166.1.621.9.1.1.0', '.1.3.6.1.4.1.1166.1.621.14.2.0']);
        $device->version  = $arrisdsr_data['.1.3.6.1.4.1.1166.1.621.9.2.3.0'] ?? null;
        $device->serial   = $arrisdsr_data['.1.3.6.1.4.1.1166.1.621.9.1.1.0'] ?? null;
        $device->hardware = $arrisdsr_data['.1.3.6.1.4.1.1166.1.621.14.2.0'] ?? null;
    }

    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.11.9.0';
        return array(
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
            )
        );
    }
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.16.6.8.0';
        return array(
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
            )
        );
    }

    public function discoverWirelessQuality()
    {
        $oid = '.1.3.6.1.4.1.1166.1.621.11.8.0';
        return array(
            new WirelessSensor(
                'quality',
                $this->getDeviceId(),
                $oid,
                'arris-dsr4410md',
                0,
                'Receive Quality'
            )
        );
    }
}
