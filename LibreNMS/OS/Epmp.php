<?php
/**
 * Epmp.php
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
 *
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Epmp extends OS implements
    OSPolling,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessFrequencyDiscovery,
    WirelessClientsDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $data = \SnmpQuery::get([
            'CAMBIUM-PMP80211-MIB::wirelessInterfaceMode.0',
            'CAMBIUM-PMP80211-MIB::cambiumSubModeType.0',
        ])->values();

        $epmp_ap = $data['CAMBIUM-PMP80211-MIB::wirelessInterfaceMode.0'] ?? null;
        $epmp_number = $data['CAMBIUM-PMP80211-MIB::cambiumSubModeType.0'] ?? null;

        if ($epmp_ap == 1) {
            $device->hardware = $epmp_number == 5 ? 'ePTP Master' : 'ePMP AP';
        } elseif ($epmp_ap == 2) {
            $device->hardware = $epmp_number == 4 ? 'ePTP Slave' : 'ePMP SM';
        }
    }

    public function pollOS(): void
    {
        $device = $this->getDeviceArray();

        $cambiumGPSNumTrackedSat = snmp_get($device, 'cambiumGPSNumTrackedSat.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        $cambiumGPSNumVisibleSat = snmp_get($device, 'cambiumGPSNumVisibleSat.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        if (is_numeric($cambiumGPSNumTrackedSat) && is_numeric($cambiumGPSNumVisibleSat)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('numTracked', 'GAUGE', 0, 100000)
                ->addDataset('numVisible', 'GAUGE', 0, 100000);
            $fields = [
                'numTracked' => $cambiumGPSNumTrackedSat,
                'numVisible' => $cambiumGPSNumVisibleSat,
            ];
            $tags = compact('rrd_def');
            data_update($device, 'cambium-epmp-gps', $tags, $fields);
            $this->enableGraph('cambium_epmp_gps');
        }

        $cambiumSTAUplinkMCSMode = snmp_get($device, 'cambiumSTAUplinkMCSMode.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        $cambiumSTADownlinkMCSMode = snmp_get($device, 'cambiumSTADownlinkMCSMode.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        if (is_numeric($cambiumSTAUplinkMCSMode) && is_numeric($cambiumSTADownlinkMCSMode)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('uplinkMCSMode', 'GAUGE', -30, 30)
                ->addDataset('downlinkMCSMode', 'GAUGE', -30, 30);
            $fields = [
                'uplinkMCSMode' => $cambiumSTAUplinkMCSMode,
                'downlinkMCSMode' => $cambiumSTADownlinkMCSMode,
            ];
            $tags = compact('rrd_def');
            data_update($device, 'cambium-epmp-modulation', $tags, $fields);
            $this->enableGraph('cambium_epmp_modulation');
        }

        $sysNetworkEntryAttempt = snmp_get($device, 'sysNetworkEntryAttempt.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        $sysNetworkEntrySuccess = snmp_get($device, 'sysNetworkEntrySuccess.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        $sysNetworkEntryAuthenticationFailure = snmp_get($device, 'sysNetworkEntryAuthenticationFailure.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
        if (is_numeric($sysNetworkEntryAttempt) && is_numeric($sysNetworkEntrySuccess) && is_numeric($sysNetworkEntryAuthenticationFailure)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('entryAttempt', 'GAUGE', 0, 100000)
                ->addDataset('entryAccess', 'GAUGE', 0, 100000)
                ->addDataset('authFailure', 'GAUGE', 0, 100000);
            $fields = [
                'entryAttempt' => $sysNetworkEntryAttempt,
                'entryAccess' => $sysNetworkEntrySuccess,
                'authFailure' => $sysNetworkEntryAuthenticationFailure,
            ];
            $tags = compact('rrd_def');
            data_update($device, 'cambium-epmp-access', $tags, $fields);
            $this->enableGraph('cambium_epmp_access');
        }

        $multi_get_array = snmp_get_multi($device, ['ulWLanTotalAvailableFrameTimePerSecond.0', 'ulWLanTotalUsedFrameTimePerSecond.0', 'dlWLanTotalAvailableFrameTimePerSecond.0', 'dlWLanTotalUsedFrameTimePerSecond.0'], '-OQU', 'CAMBIUM-PMP80211-MIB');

        $ulWLanTotalAvailableFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::ulWLanTotalAvailableFrameTimePerSecond'] ?? null;
        $ulWLanTotalUsedFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::ulWLanTotalUsedFrameTimePerSecond'] ?? null;
        $dlWLanTotalAvailableFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::dlWLanTotalAvailableFrameTimePerSecond'] ?? null;
        $dlWLanTotalUsedFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::dlWLanTotalUsedFrameTimePerSecond'] ?? null;

        if (is_numeric($ulWLanTotalAvailableFrameTimePerSecond) && is_numeric($ulWLanTotalUsedFrameTimePerSecond) && $ulWLanTotalAvailableFrameTimePerSecond && $ulWLanTotalUsedFrameTimePerSecond) {
            $ulWlanFrameUtilization = round(($ulWLanTotalUsedFrameTimePerSecond / $ulWLanTotalAvailableFrameTimePerSecond) * 100, 2);
            $dlWlanFrameUtilization = round(($dlWLanTotalUsedFrameTimePerSecond / $dlWLanTotalAvailableFrameTimePerSecond) * 100, 2);
            d_echo($dlWlanFrameUtilization);
            d_echo($ulWlanFrameUtilization);
            $rrd_def = RrdDefinition::make()
                ->addDataset('ulwlanfrut', 'GAUGE', 0, 100000)
                ->addDataset('dlwlanfrut', 'GAUGE', 0, 100000);
            $fields = [
                'ulwlanframeutilization' => $ulWlanFrameUtilization,
                'dlwlanframeutilization' => $dlWlanFrameUtilization,
            ];
            $tags = compact('rrd_def');
            data_update($device, 'cambium-epmp-frameUtilization', $tags, $fields);
            $this->enableGraph('cambium-epmp-frameUtilization');
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
        $rssi_oid = '.1.3.6.1.4.1.17713.21.1.2.3.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLRSSI.0

        return [
            new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                $rssi_oid,
                'epmp',
                0,
                'Cambium ePMP RSSI',
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
        $snr = '.1.3.6.1.4.1.17713.21.1.2.18.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLSNR.0

        return [
            new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $snr,
                'epmp',
                0,
                'Cambium ePMP SNR',
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
        $frequency = '.1.3.6.1.4.1.17713.21.1.2.1.0'; //CAMBIUM-PMP80211-MIB::cambiumSTAConnectedRFFrequency"

        return [
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $frequency,
                'epmp',
                0,
                'Cambium ePMP Frequency',
                null
            ),
        ];
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $registeredSM = '.1.3.6.1.4.1.17713.21.1.2.10.0'; //CAMBIUM-PMP80211-MIB::cambiumAPNumberOfConnectedSTA.0

        return [
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $registeredSM,
                'epmp',
                0,
                'Client Count',
                null
            ),
        ];
    }
}
