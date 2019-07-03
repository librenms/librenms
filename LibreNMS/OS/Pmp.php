<?php
/**
 * Pmp.php
 *
 * Cambium
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
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSsrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\OS;

class Pmp extends OS implements
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessFrequencyDiscovery,
    WirelessUtilizationDiscovery,
    WirelessSsrDiscovery,
    WirelessClientsDiscovery,
    WirelessErrorsDiscovery
{

    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $rssi_oid = '.1.3.6.1.4.1.161.19.3.2.2.2.0';
        return array(
            new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                $rssi_oid,
                'pmp',
                0,
                'Cambium RSSI',
                null
            )
        );
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Formula: SNR = Signal or Rx Power - Noise Floor
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        if ($this->isAp()) {
            $snr_horizontal = '.1.3.6.1.4.1.161.19.3.1.4.1.84.2'; // WHISP-APS-MIB::signalToNoiseRatioHorizontal.2
            $snr_vertical = '.1.3.6.1.4.1.161.19.3.1.4.1.74.2'; //WHISP-APS-MIB::signalToNoiseRatioVertical.2
        } else {
            $snr_horizontal = '.1.3.6.1.4.1.161.19.3.2.2.106.0'; // WHISP-SMS-MIB::signalToNoiseRatioSMHorizontal.0
            $snr_vertical = '.1.3.6.1.4.1.161.19.3.2.2.95.0'; //WHISP-SMS-MIB::signalToNoiseRatioSMVertical.0
        }

        return array(
            new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $snr_horizontal,
                'pmp-h',
                0,
                'Cambium SNR Horizontal',
                null
            ),
            new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $snr_vertical,
                'pmp-v',
                0,
                'Cambium SNR Vertical',
                null
            )
        );
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $frequency = '.1.3.6.1.4.1.161.19.3.1.7.37.0'; //WHISP-APS-MIB::currentRadioFreqCarrier
        return array(
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $frequency,
                'pmp',
                0,
                'Frequency',
                null,
                1,
                $this->freqDivisor()
            )
        );
    }


    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        $lowdownlink  = '.1.3.6.1.4.1.161.19.3.1.12.1.1.0'; // WHISP-APS-MIB::frUtlLowTotalDownlinkUtilization
        $lowuplink    = '.1.3.6.1.4.1.161.19.3.1.12.1.2.0'; // WHISP-APS-MIB::frUtlLowTotalUplinkUtilization
        $meddownlink  = '.1.3.6.1.4.1.161.19.3.1.12.2.1.0'; // WHISP-APS-MIB::frUtlMedTotalDownlinkUtilization
        $meduplink    = '.1.3.6.1.4.1.161.19.3.1.12.2.2.0'; // WHISP-APS-MIB::frUtlMedTotalUplinkUtilization
        $highdownlink = '.1.3.6.1.4.1.161.19.3.1.12.3.1.0'; // WHISP-APS-MIB::frUtlHighTotalDownlinkUtilization
        $highuplink   = '.1.3.6.1.4.1.161.19.3.1.12.3.2.0'; // WHISP-APS-MIB::frUtlHighTotalUplinkUtilization

        // 450M Specific Utilizations
        $muSectorDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.29.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkSectorUtilization
        $muDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.30.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkMumimoUtilization
        $suDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.31.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkSumimoUtilization

        return array(
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $lowdownlink,
                'pmp-downlink',
                0,
                '1m Downlink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $lowuplink,
                'pmp-uplink',
                0,
                '1m Uplink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $meddownlink,
                'pmp-downlink',
                1,
                '5m Downlink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $meduplink,
                'pmp-uplink',
                1,
                '5m Uplink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $highdownlink,
                'pmp-downlink',
                2,
                '15m Downlink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $highuplink,
                'pmp-uplink',
                2,
                '15m Uplink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $muSectorDownlink,
                'pmp-450m-sector-downlink',
                0,
                'MU-MIMO Downlink Sector utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $muDownlink,
                'pmp-450m-downlink',
                0,
                'MU-MIMO Downlink Utilization',
                null
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $suDownlink,
                'pmp-450m-su-downlink',
                0,
                'SU-MIMO Downlink Utilization',
                null
            )
        );
    }

    /**
     * Discover wireless SSR.  This is in dB. Type is ssr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSsr()
    {
        if ($this->isAp()) {
            $ssr = '.1.3.6.1.4.1.161.19.3.1.4.1.86.2'; //WHISP-APS-MIB::signalStrengthRatio.2
        } else {
            $ssr = '.1.3.6.1.4.1.161.19.3.2.2.108.0'; //WHISP-SMSSM-MIB::signalStrengthRatio.0
        }
        return array(
            new WirelessSensor(
                'ssr',
                $this->getDeviceId(),
                $ssr,
                'pmp',
                0,
                'Cambium Signal Strength Ratio',
                null
            )
        );
    }

    /**
     * Private method to declare if device is an AP
     *
     * @return boolean
     */
    private function isAp()
    {
        $device = $this->getDevice();
        return str_contains($device['hardware'], 'AP') || str_contains($device['hardware'], 'Master');
    }

    /**
     * PMP Frequency divisor is different per model
     * using the following for production:
     * FSK 5.2, 5.4, 5.7 GHz: OID returns MHz
     * FSK 900 MHz, 2.4 GHz: OID returns 100's of KHz
     * OFDM: OID returns 10's of KHz"
     */
    private function freqDivisor()
    {
        $device = $this->getDevice();

        $types = array(
            'OFDM' => 1000,
            '5.4GHz' => 1,
            '5.2Ghz' => 1,
            '5.7Ghz' => 1,
            '2.4Ghz' => 10,
            '900Mhz' => 10
        );

        $boxType = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');

        foreach ($types as $key => $value) {
            if (str_contains($boxType, $key)) {
                return $value;
            }
        }

        return 1;
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $registeredSM = '.1.3.6.1.4.1.161.19.3.1.7.1.0'; //WHISP-APS-MIB::regCount.0
        return array(
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $registeredSM,
                'pmp',
                0,
                'Client Count',
                null
            )
        );
    }

    /**
     * Discover wireless bit errors.  This is in total bits. Type is errors.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessErrors()
    {
        $fecInErrorsCount = '.1.3.6.1.4.1.161.19.3.3.1.95.0';
        $fecOutErrorsCount = '.1.3.6.1.4.1.161.19.3.3.1.97.0';
        $fecCRCError = '.1.3.6.1.4.1.161.19.3.3.1.223.0';
        return array(
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                $fecCRCError,
                'pmp-fecCRCError',
                0,
                'CRC Errors',
                null
            ),
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                $fecOutErrorsCount,
                'pmp-fecOutErrorsCount',
                0,
                'Out Error Count',
                null
            ),
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                $fecInErrorsCount,
                'pmp-fecInErrorsCount',
                0,
                'In Error Count',
                null
            )
        );
    }
}
