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

use App\Facades\DeviceCache;
use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMcsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

class Epmp extends OS implements
    OSPolling,
    WirelessCapacityDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessClientsDiscovery,
    WirelessMcsDiscovery,
    WirelessQualityDiscovery
{
    private ?array $apConnectedStaTable = null;
    private ?bool $isAp = null;

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $data = \SnmpQuery::get([
            'CAMBIUM-PMP80211-MIB::wirelessInterfaceMode.0',
            'CAMBIUM-PMP80211-MIB::cambiumSubModeType.0',
            'CAMBIUM-PMP80211-MIB::cambiumEffectiveDeviceName.0',
            'CAMBIUM-PMP80211-MIB::cambiumEffectiveAntennaGain.0',
        ])->values();

        $epmp_ap = $data['CAMBIUM-PMP80211-MIB::wirelessInterfaceMode.0'] ?? null;
        $epmp_number = $data['CAMBIUM-PMP80211-MIB::cambiumSubModeType.0'] ?? null;
        $effective_name = trim((string) ($data['CAMBIUM-PMP80211-MIB::cambiumEffectiveDeviceName.0'] ?? ''));
        $antenna_gain = $data['CAMBIUM-PMP80211-MIB::cambiumEffectiveAntennaGain.0'] ?? null;

        if ($effective_name !== '' && strcasecmp($effective_name, 'cambiumnetworks') !== 0) {
            $device->sysName = $effective_name;
        }

        $this->persistAntennaGainAttrib($device, $antenna_gain);

        if ($epmp_ap == 1) {
            $device->hardware = $epmp_number == 5 ? 'ePTP Master' : 'ePMP AP';
        } elseif ($epmp_ap == 2) {
            $device->hardware = $epmp_number == 4 ? 'ePTP Slave' : 'ePMP SM';
        }
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $device = $this->getDeviceArray();

        $pollData = \SnmpQuery::get([
            'CAMBIUM-PMP80211-MIB::wirelessInterfaceSyncSource.0',
            'CAMBIUM-PMP80211-MIB::cambiumEffectiveAntennaGain.0',
            'CAMBIUM-PMP80211-MIB::cambiumGPSNumTrackedSat.0',
            'CAMBIUM-PMP80211-MIB::cambiumGPSNumVisibleSat.0',
            'CAMBIUM-PMP80211-MIB::cambiumGPSCurrentSyncState.0',
            'CAMBIUM-PMP80211-MIB::cambiumSTAUplinkMCSMode.0',
            'CAMBIUM-PMP80211-MIB::cambiumSTADownlinkMCSMode.0',
            'CAMBIUM-PMP80211-MIB::sysNetworkEntryAttempt.0',
            'CAMBIUM-PMP80211-MIB::sysNetworkEntrySuccess.0',
            'CAMBIUM-PMP80211-MIB::sysNetworkEntryAuthenticationFailure.0',
            'CAMBIUM-PMP80211-MIB::cambiumSTAConnectedRFFrequency.0',
            'CAMBIUM-PMP80211-MIB::cambiumAPNumberOfConnectedSTA.0',
            'CAMBIUM-PMP80211-MIB::cambiumSTADLRSSI.0',
            'CAMBIUM-PMP80211-MIB::cambiumSTADLSNR.0',
            'CAMBIUM-PMP80211-MIB::ulWLanTotalAvailableFrameTimePerSecond.0',
            'CAMBIUM-PMP80211-MIB::ulWLanTotalUsedFrameTimePerSecond.0',
            'CAMBIUM-PMP80211-MIB::dlWLanTotalAvailableFrameTimePerSecond.0',
            'CAMBIUM-PMP80211-MIB::dlWLanTotalUsedFrameTimePerSecond.0',
            'CAMBIUM-PMP80211-MIB::cambiumEthRXBytes.0',
            'CAMBIUM-PMP80211-MIB::cambiumEthTXBytes.0',
        ])->values();

        $configuredSyncSource = $pollData['CAMBIUM-PMP80211-MIB::wirelessInterfaceSyncSource.0'] ?? null;
        $antenna_gain = $pollData['CAMBIUM-PMP80211-MIB::cambiumEffectiveAntennaGain.0'] ?? null;
        $this->persistAntennaGainAttrib(DeviceCache::get($this->getDeviceId()), $antenna_gain);

        $cambiumGPSNumTrackedSat = $pollData['CAMBIUM-PMP80211-MIB::cambiumGPSNumTrackedSat.0'] ?? null;
        $cambiumGPSNumVisibleSat = $pollData['CAMBIUM-PMP80211-MIB::cambiumGPSNumVisibleSat.0'] ?? null;
        if (is_numeric($cambiumGPSNumTrackedSat) && is_numeric($cambiumGPSNumVisibleSat)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('numTracked', 'GAUGE', 0, 100000)
                ->addDataset('numVisible', 'GAUGE', 0, 100000);
            $fields = [
                'numTracked' => $cambiumGPSNumTrackedSat,
                'numVisible' => $cambiumGPSNumVisibleSat,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-gps', $tags, $fields);
            $this->enableGraph('cambium_epmp_gps');
        }

        $cambiumGPSCurrentSyncState = $pollData['CAMBIUM-PMP80211-MIB::cambiumGPSCurrentSyncState.0'] ?? null;
        if ($configuredSyncSource == 1 && is_numeric($cambiumGPSCurrentSyncState)) {
            $rrd_def = RrdDefinition::make()->addDataset('gpsSync', 'GAUGE', 0, 5);
            $fields = [
                'gpsSync' => $cambiumGPSCurrentSyncState,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-gpsSync', $tags, $fields);
            $this->enableGraph('cambium_epmp_gpsSync');
        }

        $cambiumSTAUplinkMCSMode = $pollData['CAMBIUM-PMP80211-MIB::cambiumSTAUplinkMCSMode.0'] ?? null;
        $cambiumSTADownlinkMCSMode = $pollData['CAMBIUM-PMP80211-MIB::cambiumSTADownlinkMCSMode.0'] ?? null;
        if (is_numeric($cambiumSTAUplinkMCSMode) && is_numeric($cambiumSTADownlinkMCSMode)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('uplinkMCSMode', 'GAUGE', -30, 30)
                ->addDataset('downlinkMCSMode', 'GAUGE', -30, 30);
            $fields = [
                'uplinkMCSMode' => $cambiumSTAUplinkMCSMode,
                'downlinkMCSMode' => $cambiumSTADownlinkMCSMode,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-modulation', $tags, $fields);
            $this->enableGraph('cambium_epmp_modulation');
        }

        $sysNetworkEntryAttempt = $pollData['CAMBIUM-PMP80211-MIB::sysNetworkEntryAttempt.0'] ?? null;
        $sysNetworkEntrySuccess = $pollData['CAMBIUM-PMP80211-MIB::sysNetworkEntrySuccess.0'] ?? null;
        $sysNetworkEntryAuthenticationFailure = $pollData['CAMBIUM-PMP80211-MIB::sysNetworkEntryAuthenticationFailure.0'] ?? null;
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
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-access', $tags, $fields);
            $this->enableGraph('cambium_epmp_access');
        }

        $cambiumSTAConnectedRFFrequency = $pollData['CAMBIUM-PMP80211-MIB::cambiumSTAConnectedRFFrequency.0'] ?? null;
        if (is_numeric($cambiumSTAConnectedRFFrequency)) {
            $rrd_def = RrdDefinition::make()->addDataset('freq', 'GAUGE', 0, 100000);
            $fields = [
                'freq' => $cambiumSTAConnectedRFFrequency,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-freq', $tags, $fields);
            $this->enableGraph('cambium_epmp_freq');
        }

        $cambiumAPNumberOfConnectedSTA = $pollData['CAMBIUM-PMP80211-MIB::cambiumAPNumberOfConnectedSTA.0'] ?? null;
        if (is_numeric($cambiumAPNumberOfConnectedSTA)) {
            $rrd_def = RrdDefinition::make()->addDataset('regSM', 'GAUGE', 0, 100000);
            $fields = [
                'regSM' => $cambiumAPNumberOfConnectedSTA,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-registeredSM', $tags, $fields);
            $this->enableGraph('cambium_epmp_registeredSM');
        }

        $cambiumSTADLRSSI = $pollData['CAMBIUM-PMP80211-MIB::cambiumSTADLRSSI.0'] ?? null;
        $cambiumSTADLSNR = $pollData['CAMBIUM-PMP80211-MIB::cambiumSTADLSNR.0'] ?? null;
        if (is_numeric($cambiumSTADLRSSI) && is_numeric($cambiumSTADLSNR)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('cambiumSTADLRSSI', 'GAUGE', -200, 0)
                ->addDataset('cambiumSTADLSNR', 'GAUGE', 0, 100);
            $fields = [
                'cambiumSTADLRSSI' => $cambiumSTADLRSSI,
                'cambiumSTADLSNR' => $cambiumSTADLSNR,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-RFStatus', $tags, $fields);
            $this->enableGraph('cambium_epmp_RFStatus');
        }

        $ulWLanTotalAvailableFrameTimePerSecond = $pollData['CAMBIUM-PMP80211-MIB::ulWLanTotalAvailableFrameTimePerSecond.0'] ?? null;
        $ulWLanTotalUsedFrameTimePerSecond = $pollData['CAMBIUM-PMP80211-MIB::ulWLanTotalUsedFrameTimePerSecond.0'] ?? null;
        $dlWLanTotalAvailableFrameTimePerSecond = $pollData['CAMBIUM-PMP80211-MIB::dlWLanTotalAvailableFrameTimePerSecond.0'] ?? null;
        $dlWLanTotalUsedFrameTimePerSecond = $pollData['CAMBIUM-PMP80211-MIB::dlWLanTotalUsedFrameTimePerSecond.0'] ?? null;

        if (
            is_numeric($ulWLanTotalAvailableFrameTimePerSecond) &&
            is_numeric($ulWLanTotalUsedFrameTimePerSecond) &&
            is_numeric($dlWLanTotalAvailableFrameTimePerSecond) &&
            is_numeric($dlWLanTotalUsedFrameTimePerSecond) &&
            $ulWLanTotalAvailableFrameTimePerSecond > 0 &&
            $dlWLanTotalAvailableFrameTimePerSecond > 0
        ) {
            $ulWlanFrameUtilization = Number::calculatePercent($ulWLanTotalUsedFrameTimePerSecond, $ulWLanTotalAvailableFrameTimePerSecond);
            $dlWlanFrameUtilization = Number::calculatePercent($dlWLanTotalUsedFrameTimePerSecond, $dlWLanTotalAvailableFrameTimePerSecond);
            $rrd_def = RrdDefinition::make()
                ->addDataset('ulwlanfrut', 'GAUGE', 0, 100000)
                ->addDataset('dlwlanfrut', 'GAUGE', 0, 100000);
            $fields = [
                'ulwlanfrut' => $ulWlanFrameUtilization,
                'dlwlanfrut' => $dlWlanFrameUtilization,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-frameUtilization', $tags, $fields);
            $this->enableGraph('cambium-epmp-frameUtilization');
        }

        $cambiumEthRXBytes = $pollData['CAMBIUM-PMP80211-MIB::cambiumEthRXBytes.0'] ?? null;
        $cambiumEthTXBytes = $pollData['CAMBIUM-PMP80211-MIB::cambiumEthTXBytes.0'] ?? null;
        if (is_numeric($cambiumEthRXBytes) && is_numeric($cambiumEthTXBytes)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('rxBytes', 'COUNTER', 0)
                ->addDataset('txBytes', 'COUNTER', 0);
            $fields = [
                'rxBytes' => $cambiumEthRXBytes,
                'txBytes' => $cambiumEthTXBytes,
            ];
            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($device, 'cambium-epmp-traffic', $tags, $fields);
            $this->enableGraph('cambium_epmp_traffic');
        }
    }

    private function persistAntennaGainAttrib(?Device $device, $antenna_gain): void
    {
        if (! $device) {
            return;
        }

        if (is_numeric($antenna_gain)) {
            $device->setAttrib('epmp_radio_antenna_gain_dbi', (string) $antenna_gain);
        } else {
            $device->forgetAttrib('epmp_radio_antenna_gain_dbi');
        }
    }

    public function discoverWirelessCapacity()
    {
        if (! $this->isAp()) {
            return [];
        }

        $sensors = [];

        foreach ($this->getApConnectedStaTable() as $index => $entry) {
            if (! isset($entry['connectedSTATXCapacity']) || ! is_numeric($entry['connectedSTATXCapacity'])) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Capacity,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.17713.21.1.2.30.1.19.' . $index,
                'epmp-ap-capacity',
                $index,
                $this->formatSubscriberLabel((string) $index, $entry) . ' Tx Capacity',
                $entry['connectedSTATXCapacity']
            );
        }

        return $sensors;
    }

    public function discoverWirelessDistance()
    {
        if (! $this->isAp()) {
            return [];
        }

        $sensors = [];

        foreach ($this->getApConnectedStaTable() as $index => $entry) {
            if (! isset($entry['connectedSTADistance']) || ! is_numeric($entry['connectedSTADistance'])) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Distance,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.17713.21.1.2.30.1.29.' . $index,
                'epmp-ap-distance',
                $index,
                $this->formatSubscriberLabel((string) $index, $entry) . ' Distance',
                $entry['connectedSTADistance'],
                1,
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        if ($this->isAp()) {
            $sensors = [];

            foreach ($this->getApConnectedStaTable() as $index => $entry) {
                $label = $this->formatSubscriberLabel((string) $index, $entry);

                if (isset($entry['connectedSTAULRSSI']) && is_numeric($entry['connectedSTAULRSSI'])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Rssi,
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.17713.21.1.2.30.1.4.' . $index,
                        'epmp-ap-ul',
                        $index,
                        $label . ' UL RSSI',
                        $entry['connectedSTAULRSSI']
                    );
                }

                if (isset($entry['connectedSTADLRSSI']) && is_numeric($entry['connectedSTADLRSSI'])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Rssi,
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.17713.21.1.2.30.1.5.' . $index,
                        'epmp-ap-dl',
                        $index,
                        $label . ' DL RSSI',
                        $entry['connectedSTADLRSSI']
                    );
                }
            }

            if (! empty($sensors)) {
                return $sensors;
            }
        }

        $rssi_oid = '.1.3.6.1.4.1.17713.21.1.2.3.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLRSSI.0

        return [
            new WirelessSensor(
                WirelessSensorType::Rssi,
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
        if ($this->isAp()) {
            $sensors = [];

            foreach ($this->getApConnectedStaTable() as $index => $entry) {
                $label = $this->formatSubscriberLabel((string) $index, $entry);

                if (isset($entry['connectedSTAULSNR']) && is_numeric($entry['connectedSTAULSNR'])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Snr,
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.17713.21.1.2.30.1.6.' . $index,
                        'epmp-ap-ul',
                        $index,
                        $label . ' UL SNR',
                        $entry['connectedSTAULSNR']
                    );
                }

                if (isset($entry['connectedSTADLSNR']) && is_numeric($entry['connectedSTADLSNR'])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Snr,
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.17713.21.1.2.30.1.7.' . $index,
                        'epmp-ap-dl',
                        $index,
                        $label . ' DL SNR',
                        $entry['connectedSTADLSNR']
                    );
                }
            }

            if (! empty($sensors)) {
                return $sensors;
            }
        }

        $snr = '.1.3.6.1.4.1.17713.21.1.2.18.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLSNR.0

        return [
            new WirelessSensor(
                WirelessSensorType::Snr,
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
                WirelessSensorType::Frequency,
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
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $registeredSM,
                'epmp',
                0,
                'Client Count',
                null
            ),
        ];
    }

    public function discoverWirelessQuality()
    {
        if (! $this->isAp()) {
            return [];
        }

        $sensors = [];

        foreach ($this->getApConnectedStaTable() as $index => $entry) {
            if (! isset($entry['connectedSTATXQuality']) || ! is_numeric($entry['connectedSTATXQuality'])) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Quality,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.17713.21.1.2.30.1.20.' . $index,
                'epmp-ap-quality',
                $index,
                $this->formatSubscriberLabel((string) $index, $entry) . ' Tx Quality',
                $entry['connectedSTATXQuality']
            );
        }

        return $sensors;
    }

    public function discoverWirelessMcs()
    {
        if (! $this->isAp()) {
            return [];
        }

        $sensors = [];

        foreach ($this->getApConnectedStaTable() as $index => $entry) {
            $label = $this->formatSubscriberLabel((string) $index, $entry);

            if (isset($entry['connectedSTAULMCS']) && is_numeric($entry['connectedSTAULMCS'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Mcs,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.17713.21.1.2.30.1.8.' . $index,
                    'epmp-ap-ul-mcs',
                    $index,
                    $label . ' UL MCS',
                    $entry['connectedSTAULMCS']
                );
            }

            if (isset($entry['connectedSTADLMCS']) && is_numeric($entry['connectedSTADLMCS'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Mcs,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.17713.21.1.2.30.1.9.' . $index,
                    'epmp-ap-dl-mcs',
                    $index,
                    $label . ' DL MCS',
                    $entry['connectedSTADLMCS']
                );
            }
        }

        return $sensors;
    }

    private function formatSubscriberLabel(string $index, array $entry): string
    {
        $hostname = trim((string) ($entry['connectedSTAClickTHostName'] ?? ''));
        $ip = trim((string) ($entry['connectedSTAIP'] ?? ''));
        $ip = ($ip !== '' && $ip !== '0.0.0.0') ? $ip : '';
        $mac = trim((string) ($entry['connectedSTAMAC'] ?? ''));

        if ($mac === '') {
            if ($hostname !== '') {
                return $ip !== '' ? "$hostname ($ip)" : $hostname;
            }
            if ($ip !== '') {
                return $ip;
            }

            return "Subscriber $index";
        }

        if ($hostname !== '' && $ip !== '') {
            return "$hostname ($ip) [$mac]";
        }
        if ($hostname !== '') {
            return "$hostname [$mac]";
        }
        if ($ip !== '') {
            return "$ip [$mac]";
        }

        return "[$mac]";
    }

    private function getApConnectedStaTable(): array
    {
        if ($this->apConnectedStaTable !== null) {
            return $this->apConnectedStaTable;
        }

        $walkOids = [
            'CAMBIUM-PMP80211-MIB::connectedSTAMAC',
            'CAMBIUM-PMP80211-MIB::connectedSTAIP',
            'CAMBIUM-PMP80211-MIB::connectedSTAClickTHostName',
            'CAMBIUM-PMP80211-MIB::connectedSTAULRSSI',
            'CAMBIUM-PMP80211-MIB::connectedSTADLRSSI',
            'CAMBIUM-PMP80211-MIB::connectedSTAULSNR',
            'CAMBIUM-PMP80211-MIB::connectedSTADLSNR',
            'CAMBIUM-PMP80211-MIB::connectedSTAULMCS',
            'CAMBIUM-PMP80211-MIB::connectedSTADLMCS',
            'CAMBIUM-PMP80211-MIB::connectedSTADistance',
            'CAMBIUM-PMP80211-MIB::connectedSTATXCapacity',
            'CAMBIUM-PMP80211-MIB::connectedSTATXQuality',
        ];

        $table = \SnmpQuery::hideMib()->walk($walkOids)->table(1);

        return $this->apConnectedStaTable = $table;
    }

    private function isAp(): bool
    {
        if ($this->isAp === null) {
            $this->isAp = \SnmpQuery::get('CAMBIUM-PMP80211-MIB::wirelessInterfaceMode.0')->value() == 1;
        }

        return $this->isAp;
    }
}
