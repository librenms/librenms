<?php
namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\OS;

class Ird extends OS implements WirelessFrequencyDiscovery
{
    public function discoverWirelessFrequency()
    {
        $lnbfrequency_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.1.0'; //lnbFrequency
        $satfrequency_oid = '.1.3.6.1.4.1.1070.3.1.1.104.3.2.0'; //satFrequency
        return array(
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $lnbfrequency_oid,
                'lnbfrequency',
                1,
                'LNB Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $satfrequency_oid,
                'satfrequency',
                1,
                'Satellite Frequency'
            ),
        );
    }
}
