<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class Ird extends OS\Shared\Unix implements
    WirelessFrequencyDiscovery,
    WirelessRateDiscovery
{
    public function discoverWirelessFrequency()
    {
        $lnbfrequency_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.1.0'; // PBI4000P-5000P-MIB::lnbFrequency
        $satfrequency_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.2.0'; // PBI4000P-5000P-MIB::satFrequency

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $lnbfrequency_oid, 'lnbfrequency', 1, 'LNB Frequency'),
            new WirelessSensor('frequency', $this->getDeviceId(), $satfrequency_oid, 'satfrequency', 2, 'Satellite Frequency'),
        ];
    }

    public function discoverWirelessRate()
    {
        $oid_total_bitrate = '.1.3.6.1.4.1.1070.3.1.1.104.1.1.3.0'; // PBI4000P-5000P-MIB::tunerTotalBitrate
        $oid_valid_maxbitrate = '.1.3.6.1.4.1.1070.3.1.1.104.1.1.4.0'; // PBI4000P-5000P-MIB::tunerValidBitrate

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $oid_total_bitrate, 'tunertotalbitrate', 1, 'Tuner Total Bitrate', null, 1, 1),
            new WirelessSensor('rate', $this->getDeviceId(), $oid_valid_maxbitrate, 'tunervalidbittrate', 2, 'Tuner Valid Bitrate', null, 1, 1),
        ];
    }
}
