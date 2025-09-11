<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class ProtelevisionT1 extends OS implements
    WirelessFrequencyDiscovery,
    WirelessRateDiscovery
{
    public function discoverWirelessFrequency()
    {
        $rffrequency_oid = '.1.3.6.1.4.1.18086.3080.4.2.0'; // PT3080-MIB::pt3080OutputRfFrequency

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $rffrequency_oid, 'rffrequency', 1, 'Output RF Frequency', null, 1, 1000000),
        ];
    }

    public function discoverWirelessRate()
    {
        $oid_tshpbitrate = '.1.3.6.1.4.1.18086.3080.3.41.0'; // PT3080-MIB::pt3080InputTSHpBitrate

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $oid_tshpbitrate, 'pt3080InputTSHpBitrate', 1, 'Current Bitrate On Air TS', null, 1000, 1),
        ];
    }
}
