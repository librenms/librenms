<?php
/**
 *
 * ApexLynx.php
 * Trango Systems Apex Lynx Wireless Sensors for LibreNMS
 * Author: Cory Hill (cory@metavrs.com)
 *
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Modules\Wireless;
use LibreNMS\OS;

class ApexLynx extends OS implements
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
        $sensors = array();

        $sensors[] = Wireless::discover(
            'rssi',
            $this->getDeviceId(),
            $oid,
            'apex-lynx',
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

        return array(
            Wireless::discover(
                'frequency',
                $this->getDeviceId(),
                $txoid,
                'apex-lynx',
                0,
                'Tx Frequency'
            ),
            Wireless::discover(
                'frequency',
                $this->getDeviceId(),
                $rxoid,
                'apex-lynx',
                1,
                'Rx Frequency'
            )
        );
    }

    public function discoverWirelessMse()
    {
        // GIGA-PLUS-MIB::modemMSEInt
        $oid = '.1.3.6.1.4.1.5454.1.80.2.4.2.2.0';
        $sensors = array();

        $sensors[] = Wireless::discover(
            'mse',
            $this->getDeviceId(),
            $oid,
            'apex-lynx',
            1,
            'MSE'
        );
        return $sensors;
    }

    public function discoverWirelessRate()
    {
        // GIGA-PLUS-MIB::rfSpeedInt
        $oid = '.1.3.6.1.4.1.5454.1.80.3.6.4.2.0';
        $sensors = array();

        $sensors[] = Wireless::discover(
            'rate',
            $this->getDeviceId(),
            $oid,
            'apex-lynx',
            1,
            'Rate'
        );
        return $sensors;
    }

    public function discoverWirelessErrorRate()
    {
        // GIGA-PLUS-MIB::modemBER
        $oid = '.1.3.6.1.4.1.5454.1.80.2.4.1.1.0';
        $sensors = array();

        $sensors[] = Wireless::discover(
            'error-rate',
            $this->getDeviceId(),
            $oid,
            'apex-lynx',
            1,
            'BER'
        );
        return $sensors;
    }
}
