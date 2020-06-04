<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class AirosAfLtu extends OS implements
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
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
        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'airos-af-ltu', 1, 'Radio Frequency'),
        );
    }

    /**
     * Discover wireless distance.  This is in kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.23.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRemoteDistance.1
        return array(
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'airos-af-ltu', 1, 'Distance', null, 1, 1000),
        );
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $rx_power0_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.5.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRxPower0.1
        $rx_power1_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.6.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRxPower1.1
        $rx_ideal_power0_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.7.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaIdealRxPower0.1
        $rx_ideal_power1_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.8.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaIdealRxPower1.1
        $rx_power0_level_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.9.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel0.1
        $rx_power1_level_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.10.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel1.1
        $tx_eirp_oid = '.1.3.6.1.4.1.41112.1.10.1.2.6.0'; //UBNT-AFLTU-MIB::afLTUTxEIRP
        return array(
            new WirelessSensor('power', $this->getDeviceId(), $rx_power0_oid, 'airos-af-ltu-tx-chain-0', 1, 'Tx Power Chain 0'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_power1_oid, 'airos-af-ltu-tx-chain-1', 1, 'Tx Power Chain 1'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_ideal_power0_oid, 'airos-af-ltu-ideal-tx-chain-0', 1, 'Tx Ideal Power Chain 0'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_ideal_power1_oid, 'airos-af-ltu-ideal-tx-chain-1', 1, 'Tx Ideal Power Chain 1'),
            new WirelessSensor('quality', $this->getDeviceId(), $rx_power0_level_oid, 'airos-af-ltu-level-rx-chain-0', 1, 'Signal Level Chain 0'),
            new WirelessSensor('quality', $this->getDeviceId(), $rx_power1_level_oid, 'airos-af-ltu-level-rx-chain-1', 1, 'Signal Level Chain 1'),
            new WirelessSensor('power', $this->getDeviceId(), $tx_eirp_oid, 'airos-af-ltu-tx-eirp', 1, 'Tx EIRP'),
        );
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.3.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaTxCapacity.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.10.1.4.1.4.24.232.41.30.48.222'; //UBNT-AFLTU-MIB::afLTUStaRxCapacity.1
        return array(
            new WirelessSensor('rate', $this->getDeviceId(), $tx_oid, 'airos-af-ltu-tx', 1, 'Tx Rate', null, 1000),
            new WirelessSensor('rate', $this->getDeviceId(), $rx_oid, 'airos-af-ltu-rx', 1, 'Rx Rate', null, 1000),
        );
    }
}
