<?php
/**
 * Ericsson-ml.php
 *
 *
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
#use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
#use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
#use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
#use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessXpiDiscovery;
use LibreNMS\OS;

class Ericsson6600 extends OS implements
#    ProcessorDiscovery,
#    WirelessFrequencyDiscovery,
#    WirelessPowerDiscovery,
#    WirelessRssiDiscovery,
    WirelessRateDiscovery,
    WirelessXpiDiscovery,
    WirelessSnrDiscovery
{
    public function discoverWirelessSnr()
    {
        $sensors = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'xfCarrierTermSNIR', [], 'XF-RADIOLINK-RLT-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.5.1.3.1.17.' . $index,
                'ericsson-6600',
                $index,
                'SNR: ' . snmp_get($this->getDeviceArray(), 'xfCarrierTermDistinguishedName' . '.' . $index, '-Oqv', 'XF-RADIOLINK-RLT-MIB'),
                Null,
                1,
                10
            );
        }

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $sensors = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'xfCarrierTermActualCapacity', [], 'XF-RADIOLINK-RLT-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.5.1.3.1.13.' . $index,
                'ericsson-6600',
                $index,
                'Rate: ' . snmp_get($this->getDeviceArray(), 'xfCarrierTermDistinguishedName' . '.' . $index, '-Oqv', 'XF-RADIOLINK-RLT-MIB'),
                Null,
                1000,
                1
            );
        }

        return $sensors;
    }

    public function discoverWirelessXpi()
    {
        $sensors = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'xfCarrierTermXPI', [], 'XF-RADIOLINK-RLT-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'xpi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.5.1.3.1.18.' . $index,
                'ericsson-6600',
                $index,
                'XPI: ' . snmp_get($this->getDeviceArray(), 'xfCarrierTermDistinguishedName' . '.' . $index, '-Oqv', 'XF-RADIOLINK-RLT-MIB'),
                Null,
                1,
                10
            );
        }

        return $sensors;
    }

#txFrequency
#rxFrequency
#xfRFCurrentOutputPower
#xfRfCurrentInputPower


#    /**
#     * Discover wireless tx or rx power. This is in dBm. Type is power.
#     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
#     *
#     * @return array
#     */
#    public function discoverWirelessPower()
#    {
#        return [
#            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.193.81.3.4.3.1.3.1.10.2146697473', 'ericsson-6600', 1, 'Rx Power Current', Null, 1, 10),
#            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.193.81.3.4.3.1.3.1.1.2146697473', 'ericsson-6600', 2, 'Tx Power Current'),
#        ];
#    }
#
#    /**
#     * Discover wireless frequency This is in MHz. Type is frequency.
#     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
#     *
#     * @return array
#     */
#    public function discoverWirelessFrequency()
#    {
#        return [
#            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.193.81.3.4.3.1.2.1.2.2146697473', 'ericsson-6600', 1, 'Rx Frequency', Null, 1, 1000),
#            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.193.81.3.4.3.1.2.1.1.2146697473', 'ericsson-6600', 2, 'TX Frequency', Null, 1, 1000),
#        ];
#    }
#    /**
#     * Discover wireless rate This is in bps. Type is rate.
#     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
#     *
#     * @return array
#     */
#    public function discoverWirelessRate()
#    {
#        return [
#            new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.193.81.3.4.1.1.14.1.7.1', 'ericsson-6600', 1, 'Pipe Capacity', Null, 1000),
#        ];
#    }

}
