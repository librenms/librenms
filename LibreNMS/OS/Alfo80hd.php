<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Alfo80hd extends OS implements
    WirelessRssiDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery
{
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.3373.1103.39.2.1.12.1';

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'alfo80hd-rx', 1, 'RSSI'),
        ];
    }

    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.3373.1103.39.2.1.2.1';

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'alfo80hd-tx-freq', 1, 'Tx Frequency', null, 1, 1000),
        ];
    }

    public function discoverWirelessPower()
    {
        $oid = '.1.3.6.1.4.1.3373.1103.39.2.1.13.1';

        return [
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'alfo80hd-tx', 1, 'Tx Power'),
        ];
    }

    public function discoverWirelessRate()
    {
        $oid = '.1.3.6.1.4.1.3373.1103.15.4.1.';

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $oid . '17.1', 'alfo80hd-tx-rate', 1, 'Tx Rate', null, 1000, 1),
            new WirelessSensor('rate', $this->getDeviceId(), $oid . '18.1', 'alfo80hd-rx-rate', 2, 'Rx Rate', null, 1000, 1),
        ];
    }
}
