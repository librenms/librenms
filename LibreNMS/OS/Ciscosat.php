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
        $oids = snmpwalk_cache_oid($this->getDevice(), 'satSignalUncorErrCnt', array(), 'CISCO-DMN-DSG-TUNING-MIB', null, '-Ob');
        $sensors = array();
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
        $oids = snmpwalk_cache_oid($this->getDevice(), 'satSignalLevel', array(), 'CISCO-DMN-DSG-TUNING-MIB', null, '-Ob');
        $sensors = array();
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.7.' . $index,
                'ciscosat',
                $index,
                'Receive Signal Level ' .$index
            );
        }
        return $sensors;
    }
// snr - Discover C/N Link Margin

    public function discoverWirelessSnr()
    {
        $cnmargin = snmpwalk_cache_oid($this->getDevice(), 'satSignalCnMargin', array(), 'CISCO-DMN-DSG-TUNING-MIB', null, '-OQUsb');
        $dbindex = 0;
        foreach ($cnmargin as $index => $entry) {
            $snrstatus[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.6.' . $index,
                'ciscosat',
                ++$dbindex,
                'C/N Link Margin  ' .$index,
                $entry
            );
        }
// snr - Discover C/N Ratio
        $cnratio = snmpwalk_cache_oid($this->getDevice(), 'satSignalCndisp', array(), 'CISCO-DMN-DSG-TUNING-MIB', null, '-OQUsb');
        foreach ($cnratio as $index => $entry) {
            array_push($snrstatus, new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.1429.2.2.5.5.3.1.1.5.' . $index,
                'ciscosat',
                ++$dbindex,
                'C/N Ratio ' .$index,
                $entry
                )
            );
        }
        return $snrstatus;
    }
}
