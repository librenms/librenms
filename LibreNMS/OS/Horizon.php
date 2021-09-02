<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Horizon extends OS implements WirelessSnrDiscovery, WirelessPowerDiscovery
{
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.2.8.0';

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'horizon', 0, 'SNR', null, 1, 10),
        ];
    }

    public function discoverWirelessPower()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.3.7.0';

        return [
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'horizon', 0, 'Power', null, 1, 10),
        ];
    }
}
