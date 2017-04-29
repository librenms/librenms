<?php
/**
 * Mimosa.php
 *
 * Mimosa Networks
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Mimosa extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessNoiseDiscovery,
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
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $freq = $this->getCacheByIndex('mimosaCenterFreq', 'MIMOSA-NETWORKS-BFIVE-MIB');

        // both chains should be the same frequency, make sure
        $freq = array_flip($freq);
        if (count($freq) == 1) {
            $descr = 'Frequency';
        } else {
            $descr = 'Frequency: $s Chain';
        }

        foreach ($freq as $frequency => $index) {
            return array(
                new WirelessSensor(
                    'frequency',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.43356.2.1.2.6.1.1.6.' . $index,
                    'mimosa',
                    $index,
                    sprintf($descr, $this->getPolarization($polar[$index])),
                    $frequency,
                    1,
                    1000
                )
            );
        }
    }

    /**
     * Discover wireless noise.  This is in dBm. Type is noise.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoise()
    {
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDevice(), 'mimosaRxNoise', array(), 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = array();
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'noise',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.4.' . $index,
                'mimosa',
                $index,
                sprintf('Rx Noise: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaRxNoise']
            );
        }
        return $sensors;
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = array();
        $tx_oids = snmpwalk_cache_oid($this->getDevice(), 'mimosaTxPower', array(), 'MIMOSA-NETWORKS-BFIVE-MIB');
        foreach ($tx_oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.2.' . $index,
                'mimosa',
                'tx-' . $index,
                sprintf('Tx Power: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaTxPower']
            );
        }


        $rx_oids = snmpwalk_cache_oid($this->getDevice(), 'mimosaRxPower', array(), 'MIMOSA-NETWORKS-BFIVE-MIB');
        foreach ($rx_oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.3.' . $index,
                'mimosa',
                'rx-' . $index,
                sprintf('Rx Power: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaRxPower']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDevice(), 'mimosaSNR', array(), 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = array();
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.5.' . $index,
                'mimosa',
                $index,
                sprintf('SNR: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaSNR']
            );
        }
        return $sensors;
    }

    private function getPolarization($polarization)
    {
        return $polarization == 'horizontal' ? 'Horiz.' : 'Vert.';
    }
}
