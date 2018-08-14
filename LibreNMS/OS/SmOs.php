<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\OS;

class SmOs extends OS implements
    WirelessRssiDiscovery,
    WirelessFrequencyDiscovery,
    WirelessMseDiscovery
{
    public function discoverWirelessRssi()
    {
        $oids = snmpwalk_cache_oid($this->getDevice(), 'radioPrx', array(), 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = array();

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.12.1.3.' . $index,
                'sm-os',
                $index,
                'RSSI Radio ' . $index
            );
        }
        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $oids = snmpwalk_cache_oid($this->getDevice(), 'radioTxFrequency', array(), 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = array();

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.9.1.4.' . $index,
                'sm-os',
                $index,
                'Tx Frequency ' . $index
            );
        }
        return $sensors;
    }

    public function discoverWirelessMse()
    {
        $oids = snmpwalk_cache_oid($this->getDevice(), 'radioNormalizedMse', array(), 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = array();

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.12.1.5.' . $index,
                'sm-os',
                $index,
                'MSE Radio ' . $index
            );
        }
        return $sensors;
    }
}
