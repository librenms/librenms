<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class HorizonCompact extends OS implements WirelessSnrDiscovery, WirelessPowerDiscovery, WirelessRssiDiscovery, WirelessErrorsDiscovery
{
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.2.8.0';

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'horizon-compact', 0, 'SNR', null, 1, 10),
        ];
    }

    public function discoverWirelessPower()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.3.2.0';

        return [
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'horizon-compact', 0, 'Tx Power', null, 1, 10),
        ];
    }

    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.2.4.0';

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'horizon-compact', 0, 'RSL', null, 1, 10),
        ];
    }

    public function discoverWirelessErrors()
    {
        $oid = '.1.3.6.1.4.1.7262.2.2.5.1.2.15.3.0';

        return [
            new WirelessSensor('errors', $this->getDeviceId(), $oid, 'horizon-compact', 0, 'Rx Errors', null, 1, 10),
        ];
    }
}
