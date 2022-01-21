<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class AirosAf60 extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessDistanceDiscovery,
    WirelessRateDiscovery
{
    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.15.244.146.191.226.158.141'; // UI-AF60-MIB::af60StaRemoteDistance.1

        return [
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'airos-af60', 1, 'Distance', null, 1, 1000),
        ];
    }

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

    /**
     * Discover wireless rate. This is in Mbps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.7.244.146.191.226.158.141.1'; // UI-AF60-MIB::af60StaTxCapacity.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.8.244.146.191.226.158.141.1'; // UI-AF60-MIB::af60StaRxCapacity.1

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $tx_oid, 'airos-af60-tx', 1, 'Tx Capacity'),
            new WirelessSensor('rate', $this->getDeviceId(), $rx_oid, 'airos-af60-rx', 1, 'Rx Capacity'),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.3.244.146.191.226.158.141.1'; //UI-AF60-MIB::ubntRadioTxPower.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.18.244.146.191.226.158.141.1'; //UI-AF60-MIB::ubntWlStatSignal.1

        return [
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'airos-af60-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid, 'airos-af60-rx', 1, 'Rx Power'),
        ];
    }

    /**
     * Discover wireless snr. This is in dBm. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSnrDiscovery()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.4.244.146.191.226.158.141.1'; //UI-AF60-MIB::af60StaSNR.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.11.1.3.1.19.244.146.191.226.158.141.1'; //UI-AF60-MIB::af60StaRemoteSNR.1

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $tx_oid, 'airos-af60-lsnr', 1, 'Local SNR'),
            new WirelessSensor('snr', $this->getDeviceId(), $rx_oid, 'airos-af60-rsnr', 1, 'Remote SNR'),
        ];
    }
}
