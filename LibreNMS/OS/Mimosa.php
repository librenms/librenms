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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRatioDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Mimosa extends OS implements
    WirelessErrorRatioDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessErrorRatio()
    {
        $tx_oid = '.1.3.6.1.4.1.43356.2.1.2.7.3.0'; // MIMOSA-NETWORKS-BFIVE-MIB::mimosaPerTxRate
        $rx_oid = '.1.3.6.1.4.1.43356.2.1.2.7.4.0'; // MIMOSA-NETWORKS-BFIVE-MIB::mimosaPerRxRate

        return [
            new WirelessSensor(
                'error-ratio',
                $this->getDeviceId(),
                $tx_oid,
                'mimosa-tx',
                0,
                'Tx Packet Error Ratio',
                null,
                1,
                100
            ),
            new WirelessSensor(
                'error-ratio',
                $this->getDeviceId(),
                $rx_oid,
                'mimosa-rx',
                0,
                'Rx Packet Error Ratio',
                null,
                1,
                100
            ),
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
        $sensors = [];

        // ptp radios
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $bfiveFreq = $this->getCacheByIndex('mimosaCenterFreq', 'MIMOSA-NETWORKS-BFIVE-MIB');

        // both chains should be the same frequency, make sure
        if (count($bfiveFreq) == 1) {
            $descr = 'Frequency';
        } else {
            $descr = 'Frequency: %s Chain';
        }

        foreach ($bfiveFreq as $index => $frequency) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.6.' . $index,
                'mimosa-ptp',
                $index,
                sprintf($descr, $this->getPolarization($polar[$index])),
                $frequency
            );
        }

        // ptmp radios
        $ptmpRadioName = $this->getCacheByIndex('mimosaPtmpChPwrRadioName', 'MIMOSA-NETWORKS-PTMP-MIB');
        $ptmpFreq = snmpwalk_group($this->getDeviceArray(), 'mimosaPtmpChPwrCntrFreqCur', 'MIMOSA-NETWORKS-PTMP-MIB');

        foreach ($ptmpFreq as $index => $frequency) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.9.3.3.1.7.' . $index,
                'mimosa',
                $index,
                $ptmpRadioName[$index],
                $frequency['mimosaPtmpChPwrCntrFreqCur']
            );
        }

        return $sensors;
    }

    private function getPolarization($polarization)
    {
        return $polarization == 'horizontal' ? 'Horiz.' : 'Vert.';
    }

    /**
     * Discover wireless noise floor.  This is in dBm. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        // FIXME: is Noise different from Noise Floor?
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaRxNoise', [], 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.4.' . $index,
                'mimosa',
                $index,
                sprintf('Rx Noise: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaRxNoise'],
                1,
                10
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
        $sensors = [];

        // ptp radios
        $polar = $this->getCacheByIndex('mimosaPolarization', 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaTxPower', [], 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaRxPower', $oids, 'MIMOSA-NETWORKS-BFIVE-MIB');

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.2.' . $index,
                'mimosa-ptp-tx',
                $index,
                sprintf('Tx Power: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaTxPower'],
                1,
                10
            );
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.3.' . $index,
                'mimosa-ptp-rx',
                $index,
                sprintf('Rx Power: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaRxPower'],
                1,
                10
            );
        }

        // ptmp radios
        $ptmpRadioName = $this->getCacheByIndex('mimosaPtmpChPwrRadioName', 'MIMOSA-NETWORKS-PTMP-MIB');
        $ptmpTxPow = snmpwalk_group($this->getDeviceArray(), 'mimosaPtmpChPwrTxPowerCur', 'MIMOSA-NETWORKS-PTMP-MIB');

        foreach ($ptmpTxPow as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.9.3.3.1.10.' . $index,
                'mimosa-tx',
                $index,
                'Tx Power: ' . $ptmpRadioName[$index],
                $entry['mimosaPtmpChPwrTxPowerCur']
            );
        }

        $ptmpRxPow = snmpwalk_group($this->getDeviceArray(), 'mimosaPtmpChPwrMinRxPower', 'MIMOSA-NETWORKS-PTMP-MIB');

        foreach ($ptmpRxPow as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.9.3.3.1.12.' . $index,
                'mimosa-rx',
                $index,
                'Min Rx Power: ' . $ptmpRadioName[$index],
                $entry['mimosaPtmpChPwrMinRxPower']
            );
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
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaTxPhy', [], 'MIMOSA-NETWORKS-BFIVE-MIB');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaRxPhy', $oids, 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.2.1.2.' . $index,
                'mimosa-tx',
                $index,
                "Stream $index Tx Rate",
                $entry['mimosaTxPhy'],
                1000000
            );
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.2.1.5.' . $index,
                'mimosa-rx',
                $index,
                "Stream $index Rx Rate",
                $entry['mimosaRxPhy'],
                1000000
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
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'mimosaSNR', [], 'MIMOSA-NETWORKS-BFIVE-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.43356.2.1.2.6.1.1.5.' . $index,
                'mimosa',
                $index,
                sprintf('SNR: %s Chain', $this->getPolarization($polar[$index])),
                $entry['mimosaSNR'],
                1,
                10
            );
        }

        return $sensors;
    }
}
