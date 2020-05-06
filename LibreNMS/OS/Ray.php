<?php
/**
 * Ray.php
 *
 * Racom.eu
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Martin Kukal
 * @author     Martin22 <martin@kukal.cz>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class Ray extends OS implements 
    ProcessorDiscovery, 
    WirelessFrequencyDiscovery, 
    WirelessPowerDiscovery, 
    WirelessRssiDiscovery, 
    WirelessRateDiscovery, 
    WirelessSnrDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        // RAY-MIB::useCpu has no index, so it won't work in yaml

        return array(
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '.1.3.6.1.4.1.33555.1.1.5.1',
                0
            )
        );
    } 

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {

        $oid_tx = '.1.3.6.1.4.1.33555.1.2.1.4'; // RAY-MIB::txFreq.0
        $oid_rx = '.1.3.6.1.4.1.33555.1.2.1.4'; // RAY-MIB::rxFreq.0
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid_tx, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                // RAY-MIB::txFreq.0
                new WirelessSensor('frequency', $this->getDeviceId(), $oid_tx, 'racom-tx', 1, 'TX Frequency', null, 1, 1000),
            );
        } else {
            return array(
                // RAY-MIB::txFreq.0
                new WirelessSensor('frequency', $this->getDeviceId(), $oid_tx . '.0', 'racom-tx', 1, 'TX Frequency', null, 1, 1000),
            );
        }
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid_rx, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                // RAY-MIB::rxFreq.0
                new WirelessSensor('frequency', $this->getDeviceId(), $oid_rx, 'racom-rx', 1, 'RX Frequency', null, 1, 1000),
            );
        } else {
            return array(
                // RAY-MIB::rxFreq.0
                new WirelessSensor('frequency', $this->getDeviceId(), $oid_rx . '.0', 'racom-rx', 1, 'RX Frequency', null, 1, 1000),
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
        $oid_rfpowercur = '.1.3.6.1.4.1.33555.1.2.1.17';
        $oid_rfpowerconf = '.1.3.6.1.4.1.33555.1.2.1.12';

        $cmd = gen_snmpget_cmd($this->getDevice(), $oid_rfpowercur, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                // RAY-MIB::rfPowerCurrent.0
                new WirelessSensor('power', $this->getDeviceId(), $oid_rfpowercur, 'racom-pow-cur', 1, 'Tx Power Current'),
            );
        } else {
            return array(
                // RAY-MIB::rfPowerCurrent.0
                new WirelessSensor('power', $this->getDeviceId(), $oid_rfpowercur . '.0', 'racom-pow-cur', 1, 'Tx Power Current'),
            );
        }
        $cmd = gen_snmpget_cmd($this->getDevice(), '$oid_rfpowerconf', '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                //RAY-MIB::rfPowerConfigured.0
                new WirelessSensor('power', $this->getDeviceId(), $oid_rfpowerconf, 'racom-pow-conf', 1, 'Tx Power Configured'),
            );
        } else {
            return array(
                //RAY-MIB::rfPowerConfigured.0
                new WirelessSensor('power', $this->getDeviceId(), $oid_rfpowerconf . '.0', 'racom-pow-conf', 1, 'Tx Power Configured'),
            );
        }
    } 

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.33555.1.3.2.1'; // RAY-MIB::rss.0
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'racom', 1, 'RSSI', null, 1, 10),
            );
        } else {
            return array(
                new WirelessSensor('rssi', $this->getDeviceId(), $oid . '.0', 'racom', 1, 'RSSI', null, 1, 10),
            );
        }
    } 

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.33555.1.3.2.2'; // RAY-MIB::snr.0
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                new WirelessSensor('snr', $this->getDeviceId(), $oid, 'racom', 1, 'CINR', null, 1, 10),
            );
        } else {
            return array(
                new WirelessSensor('snr', $this->getDeviceId(), $oid . '.0', 'racom', 1, 'CINR', null, 1, 10),
            );
        }
    } 

    /**
     * Discover wireless RATE.  This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRate()
    {
        $oid_bitrate = '.1.3.6.1.4.1.33555.1.2.1.13'; // RAY-MIB::netBitrate.0
        $oid_maxbitrate = '.1.3.6.1.4.1.33555.1.2.1.14'; // RAY-MIB::maxNetBitrate.0
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid_bitrate, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                new WirelessSensor('rate', $this->getDeviceId(), $oid_bitrate, 'racom-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            );
        } else {
            return array(
                new WirelessSensor('rate', $this->getDeviceId(), $oid_bitrate . '.0', 'racom-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            );
        }
        $cmd = gen_snmpget_cmd($this->getDevice(), $oid_maxbitrate, '-Oqv', 'RAY-MIB', 'ray');
        $data = trim(external_exec($cmd), "\" \n\r");
        if (!preg_match('/(No Such Instance)/i', $data))
        {
            return array(
                new WirelessSensor('rate', $this->getDeviceId(), $oid_maxbitrate, 'racom-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000, 1),
            );
        } else {
            return array(
                new WirelessSensor('rate', $this->getDeviceId(), $oid_maxbitrate . '.0', 'racom-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000, 1),
            );
        }
    }
}

