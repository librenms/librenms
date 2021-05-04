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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSsrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Pmp extends OS implements
    OSPolling,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessFrequencyDiscovery,
    WirelessUtilizationDiscovery,
    WirelessSsrDiscovery,
    WirelessClientsDiscovery,
    WirelessErrorsDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['boxDeviceType.0', 'bhTimingMode.0', 'boxDeviceTypeID.0'], '-OQUs', 'WHISP-BOX-MIBV2-MIB');
        $device->features = $data['boxDeviceType.0'] ?? null;

        $ptp = [
            'BHUL450' => 'PTP 450',
            'BHUL' => 'PTP 230',
            'BH20' => 'PTP 100',
        ];

        foreach ($ptp as $desc => $model) {
            if (Str::contains($device->features, $desc)) {
                $hardware = $model . ' ' . str_replace(['timing', 'timeing'], '', $data['bhTimingMode.0']);
                $device->version = $data['boxDeviceTypeID.0'] ?? $device->version;
                break;
            }
        }

        $pmp = [
            'MU-MIMO OFDM' => 'PMP 450m',
            'MIMO OFDM' => 'PMP 450',
            'OFDM' => 'PMP 430',
        ];

        if (! isset($hardware)) {
            $hardware = 'PMP 100';
            foreach ($pmp as $desc => $model) {
                if (Str::contains($device->features, $desc)) {
                    $hardware = $model;
                    break;
                }
            }
            if (Str::contains($device->sysDescr, 'AP')) {
                $hardware .= ' AP';
            } elseif (Str::contains($device->sysDescr, 'SM')) {
                $hardware .= ' SM';
            }
        }

        $device->hardware = $hardware;
    }

    public function pollOS()
    {
        // Migrated to Wireless Sensor
        $fec = snmp_get_multi_oid($this->getDeviceArray(), ['fecInErrorsCount.0', 'fecOutErrorsCount.0', 'fecCRCError.0'], '-OQUs', 'WHISP-BOX-MIBV2-MIB');
        if (is_numeric($fec['fecInErrorsCount.0']) && is_numeric($fec['fecOutErrorsCount.0'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('fecInErrorsCount', 'GAUGE', 0, 100000)
                ->addDataset('fecOutErrorsCount', 'GAUGE', 0, 100000);

            $fields = [
                'fecInErrorsCount' => $fec['fecInErrorsCount.0'],
                'fecOutErrorsCount' => $fec['fecOutErrorsCount.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-errorCount', $tags, $fields);
            $this->enableGraph('canopy_generic_errorCount');
        }

        // Migrated to Wireless Sensor
        if (is_numeric($fec['fecCRCError.0'])) {
            $rrd_def = RrdDefinition::make()->addDataset('crcErrors', 'GAUGE', 0, 100000);
            $fields = [
                'crcErrors' => $fec['fecCRCError.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-crcErrors', $tags, $fields);
            $this->enableGraph('canopy_generic_crcErrors');
        }

        $jitter = snmp_get($this->getDeviceArray(), 'jitter.0', '-Ovqn', 'WHISP-SM-MIB');
        if (is_numeric($jitter)) {
            $rrd_def = RrdDefinition::make()->addDataset('jitter', 'GAUGE', 0, 20);
            $fields = [
                'jitter' => $jitter,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-jitter', $tags, $fields);
            $this->enableGraph('canopy_generic_jitter');
            unset($rrd_def, $jitter);
        }

        $multi_get_array = snmp_get_multi($this->getDeviceArray(), ['regCount.0', 'regFailureCount.0'], '-OQU', 'WHISP-APS-MIB');
        d_echo($multi_get_array);
        $registered = $multi_get_array[0]['WHISP-APS-MIB::regCount'];
        $failed = $multi_get_array[0]['WHISP-APS-MIB::regFailureCount'];

        if (is_numeric($registered) && is_numeric($failed)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('regCount', 'GAUGE', 0, 15000)
                ->addDataset('failed', 'GAUGE', 0, 15000);
            $fields = [
                'regCount' => $registered,
                'failed' => $failed,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-regCount', $tags, $fields);
            $this->enableGraph('canopy_generic_regCount');
            unset($rrd_def, $registered, $failed);
        }

        $visible = str_replace('"', '', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.161.19.3.4.4.7.0', '-Ovqn', ''));
        $tracked = str_replace('"', '', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.161.19.3.4.4.8.0', '-Ovqn', ''));
        if (is_numeric($visible) && is_numeric($tracked)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('visible', 'GAUGE', 0, 1000)
                ->addDataset('tracked', 'GAUGE', 0, 1000);
            $fields = [
                'visible' => floatval($visible),
                'tracked' => floatval($tracked),
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-gpsStats', $tags, $fields);
            $this->enableGraph('canopy_generic_gpsStats');
        }

        $radio = snmp_get_multi_oid($this->getDeviceArray(), ['radioDbmInt.0', 'minRadioDbm.0', 'maxRadioDbm.0', 'radioDbmAvg.0'], '-OQUs', 'WHISP-SM-MIB');
        if (is_numeric($radio['radioDbmInt.0']) && is_numeric($radio['minRadioDbm.0']) && is_numeric($radio['maxRadioDbm.0']) && is_numeric($radio['radioDbmAvg.0'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('dbm', 'GAUGE', -100, 0)
                ->addDataset('min', 'GAUGE', -100, 0)
                ->addDataset('max', 'GAUGE', -100, 0)
                ->addDataset('avg', 'GAUGE', -100, 0);

            $fields = [
                'dbm' => $radio['radioDbmInt.0'],
                'min' => $radio['minRadioDbm.0'],
                'max' => $radio['maxRadioDbm.0'],
                'avg' => $radio['radioDbmAvg.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-radioDbm', $tags, $fields);
            $this->enableGraph('canopy_generic_radioDbm');
        }

        $dbm = snmp_get_multi_oid($this->getDeviceArray(), ['linkRadioDbmHorizontal.2', 'linkRadioDbmVertical.2'], '-OQUs', 'WHISP-APS-MIB');
        if (is_numeric($dbm['linkRadioDbmHorizontal.2']) && is_numeric($dbm['linkRadioDbmVertical.2'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('horizontal', 'GAUGE', -100, 0)
                ->addDataset('vertical', 'GAUGE', -100, 0);
            $fields = [
                'horizontal' => $dbm['linkRadioDbmHorizontal.2'],
                'vertical' => $dbm['linkRadioDbmVertical.2'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-450-linkRadioDbm', $tags, $fields);
            $this->enableGraph('canopy_generic_450_linkRadioDbm');
        }

        $lastLevel = str_replace('"', '', snmp_get($this->getDeviceArray(), 'lastPowerLevel.2', '-Ovqn', 'WHISP-APS-MIB'));
        if (is_numeric($lastLevel)) {
            $rrd_def = RrdDefinition::make()->addDataset('last', 'GAUGE', -100, 0);
            $fields = [
                'last' => $lastLevel,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-450-powerlevel', $tags, $fields);
            $this->enableGraph('canopy_generic_450_powerlevel');
        }

        $vertical = str_replace('"', '', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.161.19.3.2.2.117.0', '-Ovqn', ''));
        $horizontal = str_replace('"', '', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.161.19.3.2.2.118.0', '-Ovqn', ''));
        $combined = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.161.19.3.2.2.21.0', '-Ovqn', '');
        if (is_numeric($vertical) && is_numeric($horizontal) && is_numeric($combined)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('vertical', 'GAUGE', -150, 0)
                ->addDataset('horizontal', 'GAUGE', -150, 0)
                ->addDataset('combined', 'GAUGE', -150, 0);
            $fields = [
                'vertical' => floatval($vertical),
                'horizontal' => floatval($horizontal),
                'combined' => $combined,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-signalHV', $tags, $fields);
            $this->enableGraph('canopy_generic_signalHV');
            unset($rrd_def, $vertical, $horizontal, $combined);
        }

        $horizontal = str_replace('"', '', snmp_get($this->getDeviceArray(), 'radioDbmHorizontal.0', '-Ovqn', 'WHISP-SM-MIB'));
        $vertical = str_replace('"', '', snmp_get($this->getDeviceArray(), 'radioDbmVertical.0', '-Ovqn', 'WHISP-SM-MIB'));
        if (is_numeric($horizontal) && is_numeric($vertical)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('horizontal', 'GAUGE', -100, 100)
                ->addDataset('vertical', 'GAUGE', -100, 100);

            $fields = [
                'horizontal' => $horizontal,
                'vertical' => $vertical,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'canopy-generic-450-slaveHV', $tags, $fields);
            $this->enableGraph('canopy_generic_450_slaveHV');
        }
    }

    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $rssi_oid = '.1.3.6.1.4.1.161.19.3.2.2.2.0';

        return [
            new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                $rssi_oid,
                'pmp',
                0,
                'Cambium RSSI',
                null
            ),
        ];
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

        return [
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
            ),
        ];
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

        return [
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
            ),
        ];
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        $lowdownlink = '.1.3.6.1.4.1.161.19.3.1.12.1.1.0'; // WHISP-APS-MIB::frUtlLowTotalDownlinkUtilization
        $lowuplink = '.1.3.6.1.4.1.161.19.3.1.12.1.2.0'; // WHISP-APS-MIB::frUtlLowTotalUplinkUtilization
        $meddownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.1.0'; // WHISP-APS-MIB::frUtlMedTotalDownlinkUtilization
        $meduplink = '.1.3.6.1.4.1.161.19.3.1.12.2.2.0'; // WHISP-APS-MIB::frUtlMedTotalUplinkUtilization
        $highdownlink = '.1.3.6.1.4.1.161.19.3.1.12.3.1.0'; // WHISP-APS-MIB::frUtlHighTotalDownlinkUtilization
        $highuplink = '.1.3.6.1.4.1.161.19.3.1.12.3.2.0'; // WHISP-APS-MIB::frUtlHighTotalUplinkUtilization

        // 450M Specific Utilizations
        $muSectorDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.29.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkSectorUtilization
        $muDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.30.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkMumimoUtilization
        $suDownlink = '.1.3.6.1.4.1.161.19.3.1.12.2.31.0'; // WHISP-APS-MIB::frUtlMedMumimoDownlinkSumimoUtilization

        return [
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
            ),
        ];
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

        return [
            new WirelessSensor(
                'ssr',
                $this->getDeviceId(),
                $ssr,
                'pmp',
                0,
                'Cambium Signal Strength Ratio',
                null
            ),
        ];
    }

    /**
     * Private method to declare if device is an AP
     *
     * @return bool
     */
    private function isAp()
    {
        $device = $this->getDeviceArray();

        return Str::contains($device['hardware'], 'AP') || Str::contains($device['hardware'], 'Master');
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
        $device = $this->getDeviceArray();

        $types = [
            'OFDM' => 1000,
            '5.4GHz' => 1,
            '5.2Ghz' => 1,
            '5.7Ghz' => 1,
            '2.4Ghz' => 10,
            '900Mhz' => 10,
        ];

        $boxType = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');

        foreach ($types as $key => $value) {
            if (Str::contains($boxType, $key)) {
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

        return [
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $registeredSM,
                'pmp',
                0,
                'Client Count',
                null
            ),
        ];
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

        return [
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
            ),
        ];
    }
}
