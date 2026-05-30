<?php

/**
 * Edgecos.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @copyright  2026 Frederik Kriewitz
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     Frederik Kriewitz <frederik@kriewitz.eu>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Mempool;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use SnmpQuery;

class Edgecos extends OS implements MempoolsDiscovery, ProcessorDiscovery, TransceiverDiscovery
{
    public function discoverMempools()
    {
        $mib = $this->findMib();
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['memoryTotal.0', 'memoryFreed.0', 'memoryAllocated.0'], '-OUQs', $mib);

        if (empty($data)) {
            return new Collection();
        }

        $mempool = new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'edgecos',
            'mempool_class' => 'system',
            'mempool_precision' => 1,
            'mempool_descr' => 'Memory',
            'mempool_perc_warn' => 90,
        ]);

        if (! empty($data['memoryAllocated.0'])) {
            $mempool->mempool_used_oid = match ($mib) {
                'ECS2100-MIB' => '.1.3.6.1.4.1.259.10.1.43.1.39.3.2.0',
                'ECS3510-MIB' => '.1.3.6.1.4.1.259.10.1.27.1.39.3.2.0',
                'ECS4100-52T-MIB' => '.1.3.6.1.4.1.259.10.1.46.1.39.3.2.0',
                'ECS4110-MIB' => '.1.3.6.1.4.1.259.10.1.39.1.39.3.2.0',
                'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.39.3.2.0',
                'ECS4210-MIB' => '.1.3.6.1.4.1.259.10.1.42.101.1.39.3.2.0',
                'ECS4510-MIB' => '.1.3.6.1.4.1.259.10.1.24.1.39.3.2.0',
                'ECS4610-24F-MIB' => '.1.3.6.1.4.1.259.10.1.5.1.39.3.2.0',
                'ES3510MA-MIB' => '.1.3.6.1.4.1.259.8.1.11.1.39.3.2.0',
                'ES3528MO-MIB' => '.1.3.6.1.4.1.259.6.10.94.1.39.3.2.0',
                'ES3528MV2-MIB' => '.1.3.6.1.4.1.259.10.1.22.1.39.3.2.0',
                default => null,
            };
        } else {
            $mempool->mempool_free_oid = match ($mib) {
                'ECS2100-MIB' => '.1.3.6.1.4.1.259.10.1.43.1.39.3.3.0',
                'ECS3510-MIB' => '.1.3.6.1.4.1.259.10.1.27.1.39.3.3.0',
                'ECS4100-52T-MIB' => '.1.3.6.1.4.1.259.10.1.46.1.39.3.3.0',
                'ECS4110-MIB' => '.1.3.6.1.4.1.259.10.1.39.1.39.3.3.0',
                'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.39.3.3.0',
                'ECS4210-MIB' => '.1.3.6.1.4.1.259.10.1.42.101.1.39.3.3.0',
                'ECS4510-MIB' => '.1.3.6.1.4.1.259.10.1.24.1.39.3.3.0',
                'ECS4610-24F-MIB' => '.1.3.6.1.4.1.259.10.1.5.1.39.3.3.0',
                'ES3510MA-MIB' => '.1.3.6.1.4.1.259.8.1.11.1.39.3.3.0',
                'ES3528MO-MIB' => '.1.3.6.1.4.1.259.6.10.94.1.39.3.3.0',
                'ES3528MV2-MIB' => '.1.3.6.1.4.1.259.10.1.22.1.39.3.3.0',
                default => null,
            };
        }

        $mempool->fillUsage($data['memoryAllocated.0'] ?? null, $data['memoryTotal.0'] ?? null, $data['memoryFreed.0']);

        return new Collection([$mempool]);
    }

    public function discoverOS(Device $device): void
    {
        $mib = $this->findMib();
        $data = snmp_get_multi($this->getDeviceArray(), ['swOpCodeVer.1', 'swProdName.0', 'swSerialNumber.1', 'swHardwareVer.1'], '-OQUs', $mib);

        $device->version = isset($data[1]['swHardwareVer'], $data[1]['swOpCodeVer']) ? trim($data[1]['swHardwareVer'] . ' ' . $data[1]['swOpCodeVer']) : null;
        $device->hardware = $data[0]['swProdName'] ?? null;
        $device->serial = $data[1]['swSerialNumber'] ?? null;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.24.')) { //ECS4510
            $oid = '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.22.')) { //ECS3528
            $oid = '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.39.')) { //ECS4110
            $oid = '.1.3.6.1.4.1.259.10.1.39.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.45.')) { //ECS4120
            $oid = '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
            $oid = '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.27.')) { //ECS3510
            $oid = '.1.3.6.1.4.1.259.10.1.27.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.8.1.11.')) { //ES3510MA
            $oid = '.1.3.6.1.4.1.259.8.1.11.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.46.')) { //ECS4100-52T
            $oid = '.1.3.6.1.4.1.259.10.1.46.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.5')) { //ECS4610-24F
            $oid = '.1.3.6.1.4.1.259.10.1.5.1.39.2.1.0';
        }

        if (isset($oid)) {
            return [
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $oid,
                    0
                ),
            ];
        }

        return parent::discoverProcessors();
    }

    /**
     * Discover transceivers.
     * Returns an array of LibreNMS\Device\Transceiver objects that have been discovered
     *
     * @return Collection<Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        $mib = $this->findMib();

        return SnmpQuery::cache()->mibs([$mib])->hideMib()->walk('portMediaInfoTable')->mapTable(function ($data, $ifIndex) {
            $distance = Number::cast($data['portMediaInfoLinklength'] ?? null);
            $wavelength = Number::cast($data['portMediaInfoWavelength'] ?? null);

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $data['portMediaInfoEthComplianceCodes'] ?? null,
                'vendor' => $data['portMediaInfoVendorName'] ?? null,
                'oui' => $data['portMediaInfoVendorOUI'] ?? null,
                'model' => $data['portMediaInfoPartNumber'] ?? null,
                'revision' => $data['portMediaInfoRevision'] ?? null,
                'serial' => $data['portMediaInfoSerialNumber'] ?? null,
                'date' => $data['portMediaInfoDateCode'] ?? null,
                'cable' => $data['portMediaInfoFiberType'] ?? null,
                'distance' => $distance,
                'wavelength' => $wavelength,
                'connector' => $data['portMediaInfoConnectorType'] ?? null,
            ]);
        })->filter();
    }

    /**
     * discover transceiver sensors
     *
     * @return void
     */
    public function discoverTransceiverSensors($types = ['dbm', 'current', 'temperature', 'voltage']): void
    {
        $mib = $this->findMib();

        $portOpticalMonitoringInfoTableOidPrefix = match ($mib) {
            'ECS2100-MIB' => '.1.3.6.1.4.1.259.10.1.43.1.2.11',
            'ECS3510-MIB' => '.1.3.6.1.4.1.259.10.1.27.1.2.11',
            'ECS4100-52T-MIB' => '.1.3.6.1.4.1.259.10.1.46.1.2.11',
            'ECS4110-MIB' => '.1.3.6.1.4.1.259.10.1.39.1.2.11',
            'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.2.11',
            'ECS4210-MIB' => '.1.3.6.1.4.1.259.10.1.42.101.1.2.11',
            'ECS4510-MIB' => '.1.3.6.1.4.1.259.10.1.24.1.2.11',
            'ES3510MA-MIB' => '.1.3.6.1.4.1.259.8.1.11.1.2.11',
            'ES3528MV2-MIB' => '.1.3.6.1.4.1.259.10.1.22.1.2.11',
            default => null,
        };

        if ($portOpticalMonitoringInfoTableOidPrefix === null) {
            return;
        }

        $table = SnmpQuery::cache()->mibs([$mib])->hideMib()->walk(['portOpticalMonitoringInfoTable', 'portTransceiverThresholdInfoTable'])->table(1);

        foreach ($table as $ifIndex => $data) {
            $ifName = PortCache::getNameFromIfIndex($ifIndex, $this->getDevice());
            if (in_array('dbm', $types)) {
                if (isset($data['portOpticalMonitoringInfoRxPower'])) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'dbm',
                        'sensor_oid' => "$portOpticalMonitoringInfoTableOidPrefix.1.6.$ifIndex",
                        'sensor_index' => "portOpticalMonitoringInfoRxPower.$ifIndex",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "$ifName Transceiver Receive Power",
                        'sensor_divisor' => 1,
                        'sensor_multiplier' => 1,
                        'sensor_limit_low' => isset($data['portTransceiverThresholdInfoRxPowerLowAlarm']) ? $data['portTransceiverThresholdInfoRxPowerLowAlarm'] / 100 : null,
                        'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoRxPowerLowWarn']) ? $data['portTransceiverThresholdInfoRxPowerLowWarn'] / 100 : null,
                        'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoRxPowerHighWarn']) ? $data['portTransceiverThresholdInfoRxPowerHighWarn'] / 100 : null,
                        'sensor_limit' => isset($data['portTransceiverThresholdInfoRxPowerHighAlarm']) ? $data['portTransceiverThresholdInfoRxPowerHighAlarm'] / 100 : null,
                        'sensor_current' => Number::cast($data['portOpticalMonitoringInfoRxPower'] ?? null),
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }

                if (isset($data['portOpticalMonitoringInfoTxPower'])) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'dbm',
                        'sensor_oid' => "$portOpticalMonitoringInfoTableOidPrefix.1.5.$ifIndex",
                        'sensor_index' => "portOpticalMonitoringInfoTxPower.$ifIndex",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "$ifName Transceiver Transmit Power",
                        'sensor_divisor' => 1,
                        'sensor_multiplier' => 1,
                        'sensor_limit_low' => isset($data['portTransceiverThresholdInfoTxPowerLowAlarm']) ? $data['portTransceiverThresholdInfoTxPowerLowAlarm'] / 100 : null,
                        'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoTxPowerLowWarn']) ? $data['portTransceiverThresholdInfoTxPowerLowWarn'] / 100 : null,
                        'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoTxPowerHighWarn']) ? $data['portTransceiverThresholdInfoTxPowerHighWarn'] / 100 : null,
                        'sensor_limit' => isset($data['portTransceiverThresholdInfoTxPowerHighAlarm']) ? $data['portTransceiverThresholdInfoTxPowerHighAlarm'] / 100 : null,
                        'sensor_current' => Number::cast($data['portOpticalMonitoringInfoTxPower'] ?? null),
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }
            }

            if (in_array('current', $types)) {
                if (isset($data['portOpticalMonitoringInfoTxBiasCurrent'])) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'current',
                        'sensor_oid' => "$portOpticalMonitoringInfoTableOidPrefix.1.4.$ifIndex",
                        'sensor_index' => "portOpticalMonitoringInfoTxBiasCurrent.$ifIndex",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "$ifName Transceiver Bias Current",
                        'sensor_divisor' => 1000,
                        'sensor_multiplier' => 1,
                        'sensor_limit_low' => isset($data['portTransceiverThresholdInfoTxBiasCurrentLowAlarm']) ? $data['portTransceiverThresholdInfoTxBiasCurrentLowAlarm'] / 100000 : null,
                        'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoTxBiasCurrentLowWarn']) ? $data['portTransceiverThresholdInfoTxBiasCurrentLowWarn'] / 100000 : null,
                        'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoTxBiasCurrentHighWarn']) ? $data['portTransceiverThresholdInfoTxBiasCurrentHighWarn'] / 100000 : null,
                        'sensor_limit' => isset($data['portTransceiverThresholdInfoTxBiasCurrentHighAlarm']) ? $data['portTransceiverThresholdInfoTxBiasCurrentHighAlarm'] / 100000 : null,
                        'sensor_current' => Number::cast($data['portOpticalMonitoringInfoTxBiasCurrent'] ?? null) / 1000,
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }
            }

            if (in_array('temperature', $types)) {
                if (isset($data['portOpticalMonitoringInfoTemperature'])) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'temperature',
                        'sensor_oid' => "$portOpticalMonitoringInfoTableOidPrefix.1.2.$ifIndex",
                        'sensor_index' => "portOpticalMonitoringInfoTemperature.$ifIndex",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "$ifName Transceiver Temperature",
                        'sensor_divisor' => 1,
                        'sensor_multiplier' => 1,
                        'sensor_limit_low' => isset($data['portTransceiverThresholdInfoTemperatureLowAlarm']) ? $data['portTransceiverThresholdInfoTemperatureLowAlarm'] / 100 : null,
                        'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoTemperatureLowWarn']) ? $data['portTransceiverThresholdInfoTemperatureLowWarn'] / 100 : null,
                        'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoTemperatureHighWarn']) ? $data['portTransceiverThresholdInfoTemperatureHighWarn'] / 100 : null,
                        'sensor_limit' => isset($data['portTransceiverThresholdInfoTemperatureHighAlarm']) ? $data['portTransceiverThresholdInfoTemperatureHighAlarm'] / 100 : null,
                        'sensor_current' => Number::cast($data['portOpticalMonitoringInfoTemperature'] ?? null),
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }
            }

            if (in_array('voltage', $types)) {
                if (isset($data['portOpticalMonitoringInfoVcc'])) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'voltage',
                        'sensor_oid' => "$portOpticalMonitoringInfoTableOidPrefix.1.3.$ifIndex",
                        'sensor_index' => "portOpticalMonitoringInfoVcc.$ifIndex",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "$ifName Transceiver Voltage",
                        'sensor_divisor' => 1,
                        'sensor_multiplier' => 1,
                        'sensor_limit_low' => isset($data['portTransceiverThresholdInfoVccLowAlarm']) ? $data['portTransceiverThresholdInfoVccLowAlarm'] / 100 : null,
                        'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoVccLowWarn']) ? $data['portTransceiverThresholdInfoVccLowWarn'] / 100 : null,
                        'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoVccHighWarn']) ? $data['portTransceiverThresholdInfoVccHighWarn'] / 100 : null,
                        'sensor_limit' => isset($data['portTransceiverThresholdInfoVccHighAlarm']) ? $data['portTransceiverThresholdInfoVccHighAlarm'] / 100 : null,
                        'sensor_current' => Number::cast($data['portOpticalMonitoringInfoVcc'] ?? null),
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }
            }
        }
    }

    /**
     * discover Power Status sensors
     *
     * @return void
     */
    public function discoverPowerStatus(): void
    {
        $mib = $this->findMib();

        $powerStatusOidPrefix = match ($mib) {
            'ECS2100-MIB' => '.1.3.6.1.4.1.259.10.1.43.1.1.3.1.8',
            'ECS3510-MIB' => '.1.3.6.1.4.1.259.10.1.27.1.1.3.1.8',
            'ECS4100-52T-MIB' => '.1.3.6.1.4.1.259.10.1.46.1.1.3.1.8',
            'ECS4110-MIB' => '.1.3.6.1.4.1.259.10.1.39.1.1.3.1.8',
            'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.1.3.1.8',
            'ECS4210-MIB' => '.1.3.6.1.4.1.259.10.1.42.101.1.1.3.1.8',
            'ECS4510-MIB' => '.1.3.6.1.4.1.259.10.1.24.1.1.3.1.8',
            'ECS4610-24F-MIB' => '.1.3.6.1.4.1.259.10.1.5.1.1.3.1.8',
            'ES3510MA-MIB' => '.1.3.6.1.4.1.259.8.1.11.1.1.3.1.8',
            'ES3528MO-MIB' => '.1.3.6.1.4.1.259.6.10.94.1.1.3.1.8',
            'ES3528MV2-MIB' => '.1.3.6.1.4.1.259.10.1.22.1.1.3.1.8',
            default => null,
        };

        if ($powerStatusOidPrefix === null) {
            return;
        }

        $table = SnmpQuery::mibs([$mib])->hideMib()->walk('switchInfoTable.switchInfoEntry.swPowerStatus')->table(1);

        //Create power state Index
        $state_name = 'edgecos-swPowerStatus';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'internalPower'],
            ['value' => 2, 'generic' => 0, 'graph' => 2, 'descr' => 'redundantPower'],
            ['value' => 3, 'generic' => 0, 'graph' => 3, 'descr' => 'internalAndRedundantPower'],
        ];
        create_state_index($state_name, states: $states);

        foreach ($table as $unit => $data) {
            // create power state sensor
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $powerStatusOidPrefix . '.' . $unit,
                'sensor_index' => "$state_name.$unit",
                'sensor_type' => $state_name,
                'sensor_descr' => "Power $unit status",
                'sensor_current' => $data['swPowerStatus'],
            ]));
        }
    }

    /**
     * discover Fan Status sensors
     *
     * @return void
     */
    public function discoverFanSensors($types = ['fanspeed', 'state']): void
    {
        $mib = $this->findMib();

        $switchFanTableOidPrefix = match ($mib) {
            'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.1.9',
            default => null,
        };

        if ($switchFanTableOidPrefix === null) {
            return;
        }
        $table = SnmpQuery::cache()->mibs([$mib])->hideMib()->walk('switchFanTable')->table(2);

        foreach ($table as $unit => $unitData) {
            foreach ($unitData as $index => $data) {
                if (in_array('fanspeed', $types)) {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'fanspeed',
                        'sensor_oid' => "$switchFanTableOidPrefix.1.6.$unit.$index",
                        'sensor_index' => "edgecos-switchFanOperSpeed.$unit.$index",
                        'sensor_type' => 'edgecos',
                        'sensor_descr' => "Fan $unit.$index speed",
                        'sensor_current' => $data['switchFanOperSpeed'],
                    ]));
                }

                if (in_array('state', $types)) {
                    //Create fan state Index
                    $state_name = 'edgecos-switchFanStatus';
                    $states = [
                        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'ok'],
                        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failure'],
                    ];
                    create_state_index($state_name, states:  $states);

                    // create fan state sensor
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'state',
                        'sensor_oid' => "$switchFanTableOidPrefix.1.3.$unit.$index",
                        'sensor_index' => "$state_name.$unit.$index",
                        'sensor_type' => $state_name,
                        'sensor_descr' => "Fan $unit.$index status",
                        'sensor_current' => $data['switchFanStatus'],
                    ]));
                }
            }
        }
    }

    /**
     * discover switch temperature sensors
     *
     * @return void
     */
    public function discoverSwitchTemperatureSensors(): void
    {
        $mib = $this->findMib();

        $switchThermalTempTableOidPrefix = match ($mib) {
            'ECS4100-52T-MIB' => '.1.3.6.1.4.1.259.10.1.46.1.1.11',
            'ECS4120-MIB' => '.1.3.6.1.4.1.259.10.1.45.1.1.11',
            'ECS4510-MIB' => '.1.3.6.1.4.1.259.10.1.24.1.1.11',
            default => null,
        };

        if ($switchThermalTempTableOidPrefix === null) {
            return;
        }
        $table = SnmpQuery::cache()->mibs([$mib])->hideMib()->walk('switchThermalTempTable.switchThermalTempEntry.switchThermalTempValue')->table(2);

        foreach ($table as $unit => $unitData) {
            foreach ($unitData as $index => $data) {
                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'temperature',
                    'sensor_oid' => "$switchThermalTempTableOidPrefix.1.3.$unit.$unit",
                    'sensor_index' => "edgecos-switchThermalTempValue.$unit.$index",
                    'sensor_type' => 'edgecos',
                    'sensor_descr' => "Switch Temperature $unit.$index",
                    'sensor_limit_low' => 0,
                    'sensor_limit_warn' => 85,
                    'sensor_limit' => 90,
                    'sensor_current' => $data['switchThermalTempValue'],
                ]));
            }
        }
    }

    /**
     * Find the MIB based on sysObjectID
     *
     * @return string
     */
    protected function findMib(): ?string
    {
        $table = [
            '.1.3.6.1.4.1.259.6.' => 'ES3528MO-MIB',
            '.1.3.6.1.4.1.259.10.1.22.' => 'ES3528MV2-MIB',
            '.1.3.6.1.4.1.259.10.1.24.' => 'ECS4510-MIB',
            '.1.3.6.1.4.1.259.10.1.39.' => 'ECS4110-MIB',
            '.1.3.6.1.4.1.259.10.1.42.' => 'ECS4210-MIB',
            '.1.3.6.1.4.1.259.10.1.27.' => 'ECS3510-MIB',
            '.1.3.6.1.4.1.259.10.1.45.' => 'ECS4120-MIB',
            '.1.3.6.1.4.1.259.8.1.11' => 'ES3510MA-MIB',
            '.1.3.6.1.4.1.259.10.1.43.' => 'ECS2100-MIB',
            '.1.3.6.1.4.1.259.10.1.46.' => 'ECS4100-52T-MIB',
            '.1.3.6.1.4.1.259.10.1.5' => 'ECS4610-24F-MIB',
        ];

        foreach ($table as $prefix => $mib) {
            if (Str::startsWith($this->getDevice()->sysObjectID, $prefix)) {
                return $mib;
            }
        }

        return null;
    }
}
