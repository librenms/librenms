<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class AirosAfLtu extends OS implements
    OSDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessQualityDiscovery,
    WirelessRateDiscovery
{
    /**
     * Discover wireless frequency.  This is in Hz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.41112.1.10.1.2.2.0'; //UBNT-AFLTU-MIB::afLTUFrequency.1

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'airos-af-ltu', 1, 'Radio Frequency'),
        ];
    }

    /**
     * Discover wireless distance.  This is in kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRemoteDistance', [], 'UBNT-AFLTU-MIB', null, '-OteQUsb');

        foreach ($oids as $index => $entry) {
            return [
                new WirelessSensor('distance', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.23.' . $index, 'airos-af-ltu', 1, 'Distance', $entry['afLTUStaRemoteDistance'], 1, 1000), //UBNT-AFLTU-MIB::afLTUStaRemoteDistance
            ];
        }

        return [];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $sensors = [];

        $tx_eirp_oid = '.1.3.6.1.4.1.41112.1.10.1.2.6.0'; //UBNT-AFLTU-MIB::afLTUTxEIRP

        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRxPower0', [], 'UBNT-AFLTU-MIB', null, '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRxPower1', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaIdealRxPower0', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaIdealRxPower1', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.5.' . $index, 'airos-af-ltu-rx-chain-0', 1, 'RX Power Chain 0', $entry['afLTUStaRxPower0']); //UBNT-AFLTU-MIB::afLTUStaRxPower0
            $sensors[] = new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.6.' . $index, 'airos-af-ltu-rx-chain-1', 1, 'RX Power Chain 1', $entry['afLTUStaRxPower1']); //UBNT-AFLTU-MIB::afLTUStaRxPower1
            $sensors[] = new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.7.' . $index, 'airos-af-ltu-ideal-rx-chain-0', 1, 'RX Ideal Power Chain 0', $entry['afLTUStaIdealRxPower0']); //UBNT-AFLTU-MIB::afLTUStaIdealRxPower0
            $sensors[] = new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.8.' . $index, 'airos-af-ltu-ideal-rx-chain-1', 1, 'RX Ideal Power Chain 1', $entry['afLTUStaIdealRxPower1']); //UBNT-AFLTU-MIB::afLTUStaIdealRxPower1
            break;
        }

        $sensors[] = new WirelessSensor('power', $this->getDeviceId(), $tx_eirp_oid, 'airos-af-ltu-tx-eirp', 1, 'TX EIRP');

        return $sensors;
    }

    /**
     * Discover wireless quality. This is a percent. Type is quality.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        $sensors = [];

        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRxPowerLevel0', [], 'UBNT-AFLTU-MIB', null, '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRxPowerLevel1', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor('quality', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.9.' . $index, 'airos-af-ltu-level-rx-chain-0', 1, 'Signal Level Chain 0', $entry['afLTUStaRxPower0']); //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel0
            $sensors[] = new WirelessSensor('quality', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.10.' . $index, 'airos-af-ltu-level-rx-chain-1', 1, 'Signal Level Chain 1', $entry['afLTUStaRxPower1']); //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel1
            break;
        }

        return $sensors;
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $sensors = [];

        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaTxCapacity', [], 'UBNT-AFLTU-MIB', null, '-OteQUsb');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'afLTUStaRxCapacity', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.3.' . $index, 'airos-af-ltu-tx', 1, 'TX Rate', $entry['afLTUStaTxCapacity'], 1000); //UBNT-AFLTU-MIB::afLTUStaTxCapacity
            $sensors[] = new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.41112.1.10.1.4.1.4.' . $index, 'airos-af-ltu-rx', 1, 'RX Rate', $entry['afLTUStaRxCapacity'], 1000); //UBNT-AFLTU-MIB::afLTUStaRxCapacity
            break;
        }

        return $sensors;
    }
}
