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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;

use LibreNMS\OS;

class Pmp extends OS implements
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessFrequencyDiscovery,
    WirelessUtilizationDiscovery
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
         $snr_horizontal = '.1.3.6.1.4.1.161.19.3.1.4.1.84.2'; // signalToNoiseRatioHorizontal.2", "-Ovqn", "WHISP-APS-MIB"
         $snr_vertical = '.1.3.6.1.4.1.161.19.3.1.4.1.74.2'; //"signalToNoiseRatioVertical.2", "-Ovqn", "WHISP-APS-MIB"
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
        $frequency = '.1.3.6.1.4.1.161.19.3.1.7.37.0'; //"WHISP-APS-MIB::currentRadioFreqCarrier"
        return array(
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $frequency,
                'pmp',
                0,
                'Frequency',
                null,
                10000
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
        $downlink = '.1.3.6.1.4.1.161.19.3.1.12.1.1.0'; //"WHISP-APS-MIB::frUtlLowTotalDownlinkUtilization"
        $uplink = '.1.3.6.1.4.1.161.19.3.1.12.1.2.0'; //"WHISP-APS-MIB::frUtlLowTotalUplinkUtilization"
        return array(
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $downlink,
                'pmp-downlink',
                0,
                'Downlink Utilization',
                null      
            ),
            new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                $uplink,
                'pmp-uplink',
                0,
                'Uplink Utilization',
                null    
            )
        );
    }
}



