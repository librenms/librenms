<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Ciscosat extends OS implements WirelessErrorsDiscovery, WirelessRssiDiscovery, WirelessSnrDiscovery
{
    public function discoverWirelessErrors()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'satSignalUncorErrCnt', [], 'CISCO-DMN-DSG-TUNING-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.12.' . $index,
                'ciscosat',
                $index,
                'Uncorrected Errors ' . $index
            );
        }

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'satSignalLevel', [], 'CISCO-DMN-DSG-TUNING-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.7.' . $index,
                'ciscosat',
                $index,
                'Receive Signal Level ' . $index
            );
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $sensors = [];

        // snr - Discover C/N Link Margin
        $cnmargin = snmpwalk_cache_oid($this->getDeviceArray(), 'satSignalCnMargin', [], 'CISCO-DMN-DSG-TUNING-MIB', null, '-OQUsb');
        foreach ($cnmargin as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.6.' . $index,
                'ciscosat-cn-margin',
                $index,
                'C/N Link Margin  ' . $index,
                $entry
            );
        }

        // snr - Discover C/N Ratio
        $cnratio = snmpwalk_cache_oid($this->getDeviceArray(), 'satSignalCndisp', [], 'CISCO-DMN-DSG-TUNING-MIB', null, '-OQUsb');
        foreach ($cnratio as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.5.' . $index,
                'ciscosat-cn-ratio',
                $index,
                'C/N Ratio ' . $index,
                $entry
            );
        }

        return $sensors;
    }
}
