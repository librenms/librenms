<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Port;
use App\Models\Sensor;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use SnmpQuery;

class Vsolution extends OS implements TransceiverDiscovery, VlanDiscovery
{
    /** @var array<string, int> ponIdx.onuIdx => ifIndex */
    private array $onuIfMap = [];

    /** @var array<int, int> ponIdx => ifIndex for PON ports */
    private array $ponIfMap = [];

    /** @var array<int, int> gePortIdx => ifIndex for GE uplink ports */
    private array $geIfMap = [];

    public function discoverTransceivers(): Collection
    {
        $this->buildPortMaps();

        $transceivers = new Collection;

        // OLT PON SFP transceivers
        $ponData = SnmpQuery::cache()->mibs(['V1600D'])->hideMib()
            ->walk('ponTransceiverTable')->table(1);

        foreach ($ponData as $ponIdx => $data) {
            $ifIndex = $this->ponIfMap[$ponIdx] ?? null;
            if ($ifIndex === null) {
                continue;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
            if ($portId === null) {
                continue;
            }

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => "pon.$ponIdx",
                'entity_physical_index' => $ifIndex,
                'type' => 'gpon-olt-sfp',
                'ddm' => true,
                'connector' => 'SC',
                'channels' => 1,
            ]));
        }

        // GE uplink SFP transceivers
        $uplinkData = SnmpQuery::cache()->mibs(['V1600GSwitch'])->hideMib()
            ->walk('upLinkOpticalTransceiverTable')->table(1);

        foreach ($uplinkData as $geIdx => $data) {
            $temp = $data['upLinktempperature'] ?? 'N/A';
            if ($temp === 'N/A' || $temp === '') {
                continue;  // No SFP inserted
            }

            $ifIndex = $this->geIfMap[(int) $geIdx] ?? null;
            if ($ifIndex === null) {
                continue;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
            if ($portId === null) {
                continue;
            }

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => "ge.$geIdx",
                'entity_physical_index' => $ifIndex,
                'type' => 'sfp',
                'vendor' => ($data['upLinkOpticalVendor'] ?? 'N/A') !== 'N/A' ? $data['upLinkOpticalVendor'] : null,
                'serial' => ($data['upLinkOpticalSerialNo'] ?? 'N/A') !== 'N/A' ? $data['upLinkOpticalSerialNo'] : null,
                'wavelength' => ($data['upLinkOpticalWavelen'] ?? 'N/A') !== 'N/A' ? (int) $data['upLinkOpticalWavelen'] : null,
                'ddm' => true,
                'channels' => 1,
            ]));
        }

        // ONU transceivers
        $onuData = SnmpQuery::cache()->mibs(['V1600G'])->hideMib()
            ->walk(['gOnuAuthInfoTable', 'gOnuDetailInfoTable'])->table(2);
        $rttData = SnmpQuery::cache()->mibs(['V1600G'])->hideMib()
            ->walk('gOnuRttTable')->table(2);

        foreach ($onuData as $ponIdx => $onus) {
            foreach ($onus as $onuIdx => $data) {
                $compoundIndex = "$ponIdx.$onuIdx";
                $ifIndex = $this->onuIfMap[$compoundIndex] ?? null;
                if ($ifIndex === null) {
                    continue;
                }

                $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
                if ($portId === null) {
                    continue;
                }

                $distance = isset($rttData[$ponIdx][$onuIdx]['gOnuRttDistance'])
                    ? (int) $rttData[$ponIdx][$onuIdx]['gOnuRttDistance'] : null;

                $transceivers->push(new Transceiver([
                    'port_id' => $portId,
                    'index' => $compoundIndex,
                    'entity_physical_index' => $ifIndex,
                    'type' => 'gpon-onu',
                    'vendor' => ! empty($data['gOnuDetailInfoVendorId']) ? $data['gOnuDetailInfoVendorId'] : null,
                    'model' => ! empty($data['gOnuModel']) ? $data['gOnuModel'] : null,
                    'serial' => $data['gOnuAuthInfoAuthInfo'] ?? null,
                    'revision' => $data['gOnuDetailInfoMainVer'] ?? null,
                    'distance' => $distance,
                    'ddm' => true,
                    'connector' => 'SC',
                    'channels' => 1,
                ]));
            }
        }

        return $transceivers;
    }

    /**
     * Discover per-ONU optical sensors and link them to transceivers.
     *
     * Called from includes/discovery/sensors/{class}/vsolution.inc.php
     */
    public function discoverOnuOpticalSensors(array $types = ['dbm', 'current', 'temperature', 'voltage']): void
    {
        $this->buildPortMaps();

        // --- OLT PON SFP sensors ---
        $ponData = SnmpQuery::cache()->mibs(['V1600D'])->hideMib()
            ->walk('ponTransceiverTable')->table(1);
        $ponDescrs = SnmpQuery::cache()->mibs(['V1600D'])->hideMib()
            ->walk('ponPortDescr')->pluck();

        $ponOidBase = '.1.3.6.1.4.1.37950.1.1.5.10.13.1.1';

        foreach ($ponData as $ponIdx => $data) {
            $ifIndex = $this->ponIfMap[(int) $ponIdx] ?? null;
            if ($ifIndex === null) {
                continue;
            }

            // Get port description for label
            $label = 'PON ' . $ponIdx;
            foreach ($ponDescrs as $oid => $descr) {
                if (str_ends_with($oid, ".$ponIdx")) {
                    $label = $descr;
                    break;
                }
            }

            $ponSensorDefs = [
                ['class' => 'temperature', 'col' => 2, 'key' => 'tempperature', 'types' => ['temperature']],
                ['class' => 'voltage', 'col' => 3, 'key' => 'voltage', 'types' => ['voltage']],
                ['class' => 'current', 'col' => 4, 'key' => 'biasCurrent', 'types' => ['current'], 'divisor' => 1000],
                ['class' => 'dbm', 'col' => 5, 'key' => 'transmitPower', 'types' => ['dbm'], 'suffix' => ' TX'],
            ];

            foreach ($ponSensorDefs as $def) {
                if (! array_intersect($def['types'], $types)) {
                    continue;
                }

                $value = $data[$def['key']] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $descr = "$label" . ($def['suffix'] ?? '');

                app('sensor-discovery')->discover(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $def['class'],
                    'sensor_oid' => "$ponOidBase.{$def['col']}.$ponIdx",
                    'sensor_index' => "vsol-pon-{$def['key']}.$ponIdx",
                    'sensor_type' => 'vsolution',
                    'sensor_descr' => $descr,
                    'sensor_divisor' => $def['divisor'] ?? 1,
                    'sensor_multiplier' => 1,
                    'sensor_current' => Number::extract($value) / ($def['divisor'] ?? 1),
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                ]));
            }
        }

        // --- GE uplink SFP sensors ---
        $uplinkData = SnmpQuery::cache()->mibs(['V1600GSwitch'])->hideMib()
            ->walk('upLinkOpticalTransceiverTable')->table(1);
        $uplinkOidBase = '.1.3.6.1.4.1.37950.1.1.5.10.13.7.1';

        foreach ($uplinkData as $geIdx => $data) {
            $temp = $data['upLinktempperature'] ?? 'N/A';
            if ($temp === 'N/A' || $temp === '') {
                continue;
            }

            $ifIndex = $this->geIfMap[(int) $geIdx] ?? null;
            if ($ifIndex === null) {
                continue;
            }

            $label = PortCache::getNameFromIfIndex($ifIndex, $this->getDevice()) ?: "GE $geIdx";

            $uplinkSensorDefs = [
                ['class' => 'temperature', 'col' => 2, 'key' => 'upLinktempperature', 'types' => ['temperature']],
                ['class' => 'voltage', 'col' => 3, 'key' => 'upLinkvoltage', 'types' => ['voltage']],
                ['class' => 'dbm', 'col' => 5, 'key' => 'upLinktransmitPower', 'types' => ['dbm'], 'suffix' => ' TX'],
                ['class' => 'dbm', 'col' => 6, 'key' => 'upLinkreceivePower', 'types' => ['dbm'], 'suffix' => ' RX'],
            ];

            foreach ($uplinkSensorDefs as $def) {
                if (! array_intersect($def['types'], $types)) {
                    continue;
                }

                $value = $data[$def['key']] ?? 'N/A';
                if ($value === 'N/A' || $value === '') {
                    continue;
                }

                $descr = "$label" . ($def['suffix'] ?? '');

                app('sensor-discovery')->discover(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $def['class'],
                    'sensor_oid' => "$uplinkOidBase.{$def['col']}.$geIdx",
                    'sensor_index' => "vsol-uplink-{$def['key']}.$geIdx",
                    'sensor_type' => 'vsolution',
                    'sensor_descr' => $descr,
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_current' => Number::extract($value),
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                ]));
            }
        }

        // --- Per-ONU optical sensors ---
        $authData = SnmpQuery::cache()->mibs(['V1600G'])->hideMib()
            ->walk('gOnuAuthInfoAuthInfo')->pluck();

        $serials = [];
        foreach ($authData as $oid => $serial) {
            if (preg_match('/(\d+\.\d+)$/', $oid, $m)) {
                $serials[$m[1]] = $serial;
            }
        }

        $opticalData = SnmpQuery::cache()->mibs(['V1600G'])->hideMib()
            ->walk('gOnuOpticalInfoTable')->table(2);

        $oidBase = '.1.3.6.1.4.1.37950.1.1.6.1.1.3.1';

        foreach ($opticalData as $ponIdx => $onus) {
            foreach ($onus as $onuIdx => $data) {
                $compoundIndex = "$ponIdx.$onuIdx";
                $ifIndex = $this->onuIfMap[$compoundIndex] ?? null;
                if ($ifIndex === null) {
                    continue;
                }

                $serial = $serials[$compoundIndex] ?? "PON$ponIdx:$onuIdx";

                // GPON Class B+ thresholds: OLT RX sensitivity -28dBm, ONU RX sensitivity -27dBm
                $sensorDefs = [
                    ['class' => 'temperature', 'col' => 3, 'key' => 'gOnuOpticalInfoTemp', 'types' => ['temperature']],
                    ['class' => 'voltage', 'col' => 4, 'key' => 'gOnuOpticalInfoVolt', 'types' => ['voltage']],
                    ['class' => 'current', 'col' => 5, 'key' => 'gOnuOpticalInfoBias', 'types' => ['current']],
                    ['class' => 'dbm', 'col' => 6, 'key' => 'gOnuOpticalInfoTxPwr', 'types' => ['dbm'], 'suffix' => ' TX',
                        'low' => 0.5, 'low_warn' => 1.0, 'high_warn' => 5.0, 'high' => 6.0],
                    ['class' => 'dbm', 'col' => 7, 'key' => 'gOnuOpticalInfoRxPwr', 'types' => ['dbm'], 'suffix' => ' RX',
                        'low' => -27, 'low_warn' => -24, 'high_warn' => -8, 'high' => -6],
                    ['class' => 'dbm', 'col' => 8, 'key' => 'gOnuOpticalInfoRxOptLevOlt', 'types' => ['dbm'], 'suffix' => ' OLT-RX',
                        'low' => -28, 'low_warn' => -25, 'high_warn' => -8, 'high' => -6],
                ];

                foreach ($sensorDefs as $def) {
                    if (! array_intersect($def['types'], $types)) {
                        continue;
                    }

                    $value = $data[$def['key']] ?? null;
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $numValue = Number::extract($value);
                    if ($numValue == 0 && $def['class'] !== 'temperature') {
                        continue;
                    }

                    $descr = "ONU $serial" . ($def['suffix'] ?? '');

                    app('sensor-discovery')->discover(new Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => $def['class'],
                        'sensor_oid' => "$oidBase.{$def['col']}.$compoundIndex",
                        'sensor_index' => "vsol-{$def['key']}.$compoundIndex",
                        'sensor_type' => 'vsolution',
                        'sensor_descr' => $descr,
                        'sensor_divisor' => 1,
                        'sensor_multiplier' => 1,
                        'sensor_current' => $numValue,
                        'sensor_limit_low' => $def['low'] ?? null,
                        'sensor_limit_low_warn' => $def['low_warn'] ?? null,
                        'sensor_limit_warn' => $def['high_warn'] ?? null,
                        'sensor_limit' => $def['high'] ?? null,
                        'entPhysicalIndex' => $ifIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'group' => 'transceiver',
                    ]));
                }
            }
        }
    }

    public function discoverVlans(): Collection
    {
        return SnmpQuery::cache()->mibs(['V1600GSwitch'])->hideMib()
            ->walk('vlanTable')
            ->mapTable(fn ($data, $vlanId) => new Vlan([
                'vlan_vlan' => (int) $vlanId,
                'vlan_domain' => 1,
                'vlan_name' => $data['vlanName'] ?? "VLAN $vlanId",
            ]));
    }

    private function buildPortMaps(): void
    {
        if (! empty($this->onuIfMap)) {
            return;
        }

        $ports = Port::where('device_id', $this->getDevice()->device_id)->get(['ifIndex', 'ifDescr']);
        foreach ($ports as $port) {
            if (preg_match('/^GE(\d+)\/(\d+)\s/', $port->ifDescr, $m)) {
                // GE uplink ports: "GE0/1 Uplink" → geIdx = slot*portCount + port
                $geIdx = (int) $m[2];
                $this->geIfMap[$geIdx] = $port->ifIndex;
            } elseif (preg_match('/GPON(\d{2})ONU(\d+)$/', $port->ifDescr, $m)) {
                // ONU virtual interfaces: GPON01ONU1 → ponIdx=1, onuIdx=1
                $ponIdx = (int) $m[1];
                $onuIdx = (int) $m[2];
                $this->onuIfMap["$ponIdx.$onuIdx"] = $port->ifIndex;
            } elseif (preg_match('/^GPON(\d+)\/(\d+)\s/', $port->ifDescr, $m)) {
                // PON port interfaces: "GPON0/1 Clients" → ponIdx=1
                $ponIdx = (int) $m[2];
                $this->ponIfMap[$ponIdx] = $port->ifIndex;
            }
        }
    }
}
