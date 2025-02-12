<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2025 CTNET BV <servicedesk@ctnet.nl>
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;

class Merakimr extends OS implements
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling
{
    public function discoverWirelessFrequency()
    {
        $mrRadioChannelOper = $this->getCacheByIndex('dot11CurrentChannel', 'IEEE802dot11-MIB');
        $sensors = [];
        $lastChannel = null;

        foreach ($mrRadioChannelOper as $index => $channel) {
            if ($lastChannel != $channel) {
                $sensors[] = new WirelessSensor(
                    'frequency',
                    $this->getDeviceId(),
                    '.1.2.840.10036.4.5.1.1.' . $index,
                    'merakimr',
                    'Radio ' . $index,
                    "Frequency (Radio $index)",
                    WirelessSensor::channelToFrequency($channel)
                );
            }
            $lastChannel = $channel;
        }

        return $sensors;
    }

    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors);
    }
}
