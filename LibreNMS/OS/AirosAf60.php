<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class AirosAf60 extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery,
    WirelessDistanceDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
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

    public function discoverWirelessDistance()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaRemoteDistance', [], 'UI-AF60-MIB', 'ubnt', '-OteQUsb');

        foreach ($oids as $index => $entry) {
            return [
                new WirelessSensor('distance', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.15.' . $index, 'airos-af60', 1, 'Distance', $entry['af60StaRemoteDistance'], 1, 1000), //UI-AF60-MIB::af60StaRemoteDistance
            ];
        }
    }

    public function discoverWirelessRate()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaTxCapacity', [], 'UI-AF60-MIB', 'ubnt', '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaRxCapacity', $oids, 'UI-AF60-MIB', 'ubnt', '-OteQUsb');

        foreach ($oids as $index => $entry) {
            return [
                new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.7.' . $index, 'airos-af60-TX', 1, 'Tx Capacity', $entry['af60StaTxCapacity'], 1, 1000), //UI-AF60-MIB::af60StaTxCapacity
                new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.8.' . $index, 'airos-af60-RX', 1, 'Rx Capacity', $entry['af60StaRxCapacity'], 1, 1000), //UI-AF60-MIB::af60StaRxCapacity
            ];
        }
    }

    public function discoverWirelessRssi()
    {
        $sensors = [];

        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaRSSI', [], 'UI-AF60-MIB', 'ubnt', '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaRemoteRSSI', $oids, 'UI-AF60-MIB', 'ubnt', '-OteQUsb');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.3.' . $index, 'airos-af60-l', 1, 'Local RSSI', $entry['af60StaRSSI'], 1); //UI-AF60-MIB::af60StaRSSI
            $sensors[] = new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.18.' . $index, 'airos-af60-r', 1, 'Remote RSSI', $entry['af60StaRemoteRSSI'], 1); //UI-AF60-MIB::af60StaRemoteRSSI
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $sensors = [];

        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaSNR', [], 'UI-AF60-MIB', 'ubnt', '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'af60StaRemoteSNR', $oids, 'UI-AF60-MIB', 'ubnt', '-OteQUsb');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.4.' . $index, 'airos-af60-l', 1, 'Local SNR', $entry['af60StaSNR'], 1); //UI-AF60-MIB::af60StaSNR
            $sensors[] = new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.11.1.3.1.19.' . $index, 'airos-af60-r', 1, 'Remote SNR', $entry['af60StaRemoteSNR'], 1); //UI-AF60-MIB::af60StaRemoteSNR
        }

        return $sensors;
    }
}
