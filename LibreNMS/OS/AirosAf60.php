<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\OS;

class AirosAf60 extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery
{
    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.41112.1.11.1.1.2.1'; // UI-AF60-MIB::af60Frequency.1

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'airos-af60', 1, 'Radio Frequency'),
        ];
    }
}
