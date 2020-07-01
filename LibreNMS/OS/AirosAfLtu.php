<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class AirosAfLtu extends OS implements
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
        $oid = snmp_getnext($this->getDevice(), '.1.3.6.1.4.1.41112.1.10.1.4.1.23', '-OnQ'); //UBNT-AFLTU-MIB::afLTUStaRemoteDistance
        if (is_string($oid)) {
            list($oid, $value) = explode('=', $oid, 2);
            $oid = trim($oid);
            $value = (int)trim($value, "\" \n\r");

            return array(
                new WirelessSensor('distance', $this->getDeviceId(), $oid, 'airos-af-ltu', 1, 'Distance', $value, 1, 1000),
            );
        }
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $oids = array();
        $sensors = array();

        $oids['rx_power0'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.5', 'class' => 'power', 'type' => 'airos-af-ltu-rx-chain-0', 'desc' => 'RX Power Chain 0']; //UBNT-AFLTU-MIB::afLTUStaRxPower0
        $oids['rx_power1'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.6', 'class' => 'power', 'type' => 'airos-af-ltu-rx-chain-1', 'desc' => 'RX Power Chain 1']; //UBNT-AFLTU-MIB::afLTUStaRxPower1
        $oids['rx_ideal_power0'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.7', 'class' => 'power', 'type' => 'airos-af-ltu-ideal-rx-chain-0', 'desc' => 'RX Ideal Power Chain 0']; //UBNT-AFLTU-MIB::afLTUStaIdealRxPower0
        $oids['rx_ideal_power1'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.8', 'class' => 'power', 'type' => 'airos-af-ltu-ideal-rx-chain-1', 'desc' => 'RX Ideal Power Chain 1']; //UBNT-AFLTU-MIB::afLTUStaIdealRxPower1

        $tx_eirp_oid = '.1.3.6.1.4.1.41112.1.10.1.2.6.0'; //UBNT-AFLTU-MIB::afLTUTxEIRP

        foreach ($oids as $index => $item) {
            $oid = snmp_getnext($this->getDevice(), $item['oid'], '-OnQ');

            if (is_string($oid)) {
                list($oid, $value) = explode('=', $oid, 2);
                $oid = trim($oid);
                $value = (int)trim($value, "\" \n\r");

                $sensors[] = new WirelessSensor($item['class'], $this->getDeviceId(), $oid, $item['type'], 1, $item['desc'], $value);
            }
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
        $oids = array();
        $sensors = array();

        $oids['rx_power0_level'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.9', 'class' => 'quality', 'type' => 'airos-af-ltu-level-rx-chain-0', 'desc' => 'Signal Level Chain 0']; //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel0
        $oids['rx_power1_level'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.10', 'class' => 'quality', 'type' => 'airos-af-ltu-level-rx-chain-1', 'desc' => 'Signal Level Chain 1']; //UBNT-AFLTU-MIB::afLTUStaRxPowerLevel1

        foreach ($oids as $index => $item) {
            $oid = snmp_getnext($this->getDevice(), $item['oid'], '-OnQ');

            if (is_string($oid)) {
                list($oid, $value) = explode('=', $oid, 2);
                $oid = trim($oid);
                $value = (int)trim($value, "\" \n\r");

                $sensors[] = new WirelessSensor($item['class'], $this->getDeviceId(), $oid, $item['type'], 1, $item['desc'], $value, 1000);
            }
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
        $oids = array();
        $sensors = array();

        $oids['tx'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.3', 'class' => 'rate', 'type' => 'airos-af-ltu-tx', 'desc' => 'TX Rate']; //UBNT-AFLTU-MIB::afLTUStaTxCapacity
        $oids['rx'] = ['oid' => '.1.3.6.1.4.1.41112.1.10.1.4.1.4', 'class' => 'rate', 'type' => 'airos-af-ltu-rx', 'desc' => 'RX Rate']; //UBNT-AFLTU-MIB::afLTUStaRxCapacity

        foreach ($oids as $index => $item) {
            $oid = snmp_getnext($this->getDevice(), $item['oid'], '-OnQ');

            if (is_string($oid)) {
                list($oid, $value) = explode('=', $oid, 2);
                $oid = trim($oid);
                $value = (int)trim($value, "\" \n\r");

                $sensors[] = new WirelessSensor($item['class'], $this->getDeviceId(), $oid, $item['type'], 1, $item['desc'], $value, 1000);
            }
        }
        return $sensors;
    }
}
