<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class HarmonyEnhanced extends OS implements WirelessRssiDiscovery, WirelessSnrDiscovery, WirelessPowerDiscovery, WirelessErrorsDiscovery
{
    public function discoverWirelessRssi()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mwrEmcRadioRSL', [], 'MWR-RADIO-MC-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7262.4.5.12.203.1.1.5.' . $index,
                'harmony_enhanced',
                $index,
                'RSL Radio ' . $index,
                null,
                null,
                10
            );
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mwrEmcRadioSNR', [], 'MWR-RADIO-MC-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7262.4.5.12.203.1.1.7.' . $index,
                'harmony_enhanced',
                $index,
                'SNR Radio ' . $index,
                null,
                null,
                10
            );
        }

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mwrEmcRadioActualTxPower', [], 'MWR-RADIO-MC-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7262.4.5.12.203.1.1.9.' . $index,
                'harmony_enhanced',
                $index,
                'TX Power Radio ' . $index,
                null,
                null,
                10
            );
        }

        return $sensors;
    }

    public function discoverWirelessErrors()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mwrEmcRadioRxErrsFrames', [], 'MWR-RADIO-MC-MIB', null, '-Ob');
        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7262.4.5.12.203.1.1.4.' . $index,
                'harmony_enhanced',
                $index,
                'RX Errors Radio ' . $index
            );
        }

        return $sensors;
    }
}
