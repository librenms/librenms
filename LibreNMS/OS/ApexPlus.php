<?php
/**
 * ApexPlus.php
 * Trango Systems Apex Plus Wireless Sensors for LibreNMS
 * Author: Cory Hill (cory@metavrs.com)
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class ApexPlus extends OS implements
    WirelessRssiDiscovery,
    WirelessFrequencyDiscovery,
    WirelessMseDiscovery,
    WirelessRateDiscovery,
    WirelessErrorRateDiscovery
{
    public function discoverWirelessRssi()
    {
        // GIGA-PLUS-MIB::rfRSSIInt
        $oid = '.1.3.6.1.4.1.5454.1.80.3.14.2.0';
        $sensors = [];
        $sensors[] = new WirelessSensor(
            'rssi',
            $this->getDeviceId(),
            $oid,
            'apex-plus',
            1,
            'RSSI'
        );

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        // GIGA-PLUS-MIB::rfTxFrequencyInt, rfRxFrequencyInt
        $txoid = '.1.3.6.1.4.1.5454.1.80.3.1.1.2.0';
        $rxoid = '.1.3.6.1.4.1.5454.1.80.3.1.2.2.0';

        return [
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $txoid,
                'apex-plus',
                0,
                'Tx Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $rxoid,
                'apex-plus',
                1,
                'Rx Frequency'
            ),
        ];
    }

    public function discoverWirelessMse()
    {
        // GIGA-PLUS-MIB::modemMSEInt
        $oid = '.1.3.6.1.4.1.5454.1.80.2.4.2.2.0';
        $sensors = [];
        $sensors[] = new WirelessSensor(
            'mse',
            $this->getDeviceId(),
            $oid,
            'apex-plus',
            1,
            'MSE'
        );

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        // GIGA-PLUS-MIB::rfSpeedInt
        $oid = '.1.3.6.1.4.1.5454.1.80.3.6.4.2.0';
        $sensors = [];
        $sensors[] = new WirelessSensor(
            'rate',
            $this->getDeviceId(),
            $oid,
            'apex-plus',
            1,
            'Rate'
        );

        return $sensors;
    }

    public function discoverWirelessErrorRate()
    {
        // GIGA-PLUS-MIB::modemBER
        $oid = '.1.3.6.1.4.1.5454.1.80.2.4.1.1.0';
        $sensors = [];
        $sensors[] = new WirelessSensor(
            'error-rate',
            $this->getDeviceId(),
            $oid,
            'apex-plus',
            1,
            'BER'
        );

        return $sensors;
    }
}
