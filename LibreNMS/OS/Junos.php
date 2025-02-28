<?php
/*
 * Junos.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Sla;
use App\Models\Transceiver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Junos extends \LibreNMS\OS implements SlaDiscovery, OSPolling, SlaPolling, TransceiverDiscovery
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverOS(Device $device): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'JUNIPER-MIB::jnxBoxDescr.0',
            'JUNIPER-MIB::jnxBoxSerialNo.0',
            'JUNIPER-VIRTUALCHASSIS-MIB::jnxVirtualChassisMemberSWVersion.0',
            'HOST-RESOURCES-MIB::hrSWInstalledName.2',
        ], '-OQUs');

        preg_match('/Juniper Networks, Inc. (?<hardware>\S+) .* kernel JUNOS (?<version>[^, ]+)[, ]/', $device->sysDescr, $parsed);
        if (isset($data[2]['hrSWInstalledName'])) {
            preg_match('/\[(.+)]/', $data[2]['hrSWInstalledName'], $parsedVersion);
        }

        $device->hardware = $data[0]['jnxBoxDescr'] ?? (isset($parsed['hardware']) ? 'Juniper ' . strtoupper($parsed['hardware']) : null);
        $device->serial = $data[0]['jnxBoxSerialNo'] ?? null;
        $device->version = $data[0]['jnxVirtualChassisMemberSWVersion'] ?? $parsedVersion[1] ?? $parsed['version'] ?? null;
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), 'jnxJsSPUMonitoringCurrentFlowSession.0', '-OUQs', 'JUNIPER-SRX5000-SPU-MONITORING-MIB');

        if (is_numeric($data[0]['jnxJsSPUMonitoringCurrentFlowSession'] ?? null)) {
            $datastore->put($this->getDeviceArray(), 'junos_jsrx_spu_sessions', [
                'rrd_def' => RrdDefinition::make()->addDataset('spu_flow_sessions', 'GAUGE', 0),
            ], [
                'spu_flow_sessions' => $data[0]['jnxJsSPUMonitoringCurrentFlowSession'],
            ]);

            $this->enableGraph('junos_jsrx_spu_sessions');
        }
    }

    public function discoverSlas(): Collection
    {
        $slas = new Collection();
        $sla_table = snmpwalk_group($this->getDeviceArray(), 'pingCtlTable', 'DISMAN-PING-MIB', 2, snmpFlags: '-OQUstX');

        if (! empty($sla_table)) {
            $sla_table = snmpwalk_group($this->getDeviceArray(), 'jnxPingResultsRttUs', 'JUNIPER-PING-MIB', 2, $sla_table, snmpFlags: '-OQUstX');
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            foreach ($sla_config as $test_key => $test_config) {
                $slas->push(new Sla([
                    'sla_nr' => hexdec(hash('crc32', $sla_key . $test_key)), // indexed by owner+test, convert to int
                    'owner' => $sla_key,
                    'tag' => $test_key,
                    'rtt_type' => $this->retrieveJuniperType($test_config['pingCtlType']),
                    'rtt' => isset($test_config['jnxPingResultsRttUs']) ? $test_config['jnxPingResultsRttUs'] / 1000 : null,
                    'status' => ($test_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                    'opstatus' => ($test_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
                ]));
            }
        }

        return $slas;
    }

    public function discoverEntityPhysical(): Collection
    {
        $entPhysical = $this->discoverBaseEntityPhysical();
        if ($entPhysical->isNotEmpty()) {
            return $entPhysical;
        }

        $chassisName = null;

        $containers = SnmpQuery::hideMib()
            ->mibs(['JUNIPER-CHASSIS-DEFINES-MIB'])
            ->walk('JUNIPER-MIB::jnxContainersTable')
            ->mapTable(function ($entry, $index) use (&$chassisName) {
                $modelName = $this->parseType($entry['jnxContainersType'] ?? null, $chassisName);
                $chassisName ??= $modelName;
                $descr = $entry['jnxContainersDescr'] ?? null;
                $within = $entry['jnxContainersWithin'] ?? 0;

                return new EntPhysical([
                    'entPhysicalIndex' => $index,
                    'entPhysicalClass' => $within == '0' ? 'chassis' : 'container',
                    'entPhysicalDescr' => $descr,
                    'entPhysicalModelName' => $modelName,
                    'entPhysicalContainedIn' => $within,
                ]);
            });

        if ($containers->isEmpty()) {
            return $containers;
        }

        return $containers->merge(SnmpQuery::hideMib()->enumStrings()
            ->mibs(['JUNIPER-CHASSIS-DEFINES-MIB'])
            ->walk('JUNIPER-MIB::jnxContentsTable')
            ->mapTable(function ($entry, $container, $indexL1, $indexL2, $indexL3) use ($chassisName, $containers) {
                // set serial for the chassis, but don't add another container
                if ($container == 1 && $indexL1 == 1 && $indexL2 == 0 && $indexL3 == 0) {
                    $chassis = $containers->firstWhere('entPhysicalClass', 'chassis');
                    if ($chassis) {
                        $chassis->entPhysicalSerialNum = $entry['jnxContentsSerialNo'] ?? null;

                        return null;
                    }
                }

                // Juniper's MIB doesn't have the same objects as the Entity MIB, so some values are made up here.
                return new EntPhysical([
                    'entPhysicalIndex' => $container + $indexL1 * 1000000 + $indexL2 * 10000 + $indexL3 * 100,
                    'entPhysicalDescr' => $entry['jnxContentsDescr'] ?? null,
                    'entPhysicalContainedIn' => $container,
                    'entPhysicalClass' => $this->parseClass($entry['jnxContentsType'] ?? null),
                    'entPhysicalName' => $entry['jnxOperatingDescr'] ?? null,
                    'entPhysicalSerialNum' => $entry['jnxContentsSerialNo'] ?? null,
                    'entPhysicalModelName' => $entry['jnxContentsPartNo'] ?? null,
                    'entPhysicalMfgName' => 'Juniper',
                    'entPhysicalVendorType' => $this->parseType($entry['jnxContentsType'] ?? null, $chassisName),
                    'entPhysicalParentRelPos' => -1,
                    'entPhysicalHardwareRev' => $entry['jnxContentsRevision'] ?? null,
                    'entPhysicalIsFRU' => isset($entry['jnxContentsSerialNo']) ? ($entry['jnxContentsSerialNo'] == 'BUILTIN' ? 'false' : 'true') : null,
                ]);
            }))->filter();
    }

    public function pollSlas($slas): void
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $data = snmpwalk_group($device, 'pingCtlRowStatus', 'DISMAN-PING-MIB', 2);
        $data = snmpwalk_group($device, 'jnxPingLastTestResultTable', 'JUNIPER-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'jnxPingResultsTable', 'JUNIPER-PING-MIB', 2, $data);

        // Get the needed information
        foreach ($slas as $sla) {
            $sla_nr = $sla->sla_nr;
            $rtt_type = $sla->rtt_type;
            $owner = $sla->owner;
            $test = $sla->tag;

            // Lets process each SLA

            // Use DISMAN-PING Status codes. 0=Good 2=Critical
            $sla->opstatus = $data[$owner][$test]['pingCtlRowStatus'] == '1' ? 0 : 2;

            $sla->rtt = ($data[$owner][$test]['jnxPingResultsAvgRttUs'] ?? 0) / 1000;
            $time = Carbon::parse($data[$owner][$test]['jnxPingResultsTime'] ?? null)->toDateTimeString();
            Log::info('SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $sla->rtt . 'ms at ' . $time);

            $collected = ['rtt' => $sla->rtt];

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'DnsQuery':
                case 'HttpGet':
                case 'HttpGetMetadata':
                    break;
                case 'IcmpEcho':
                case 'IcmpTimeStamp':
                    $icmp = [
                        'MinRttUs' => ($data[$owner][$test]['jnxPingResultsMinRttUs'] ?? 0) / 1000,
                        'MaxRttUs' => ($data[$owner][$test]['jnxPingResultsMaxRttUs'] ?? 0) / 1000,
                        'StdDevRttUs' => ($data[$owner][$test]['jnxPingResultsStdDevRttUs'] ?? 0) / 1000,
                        'ProbeResponses' => $data[$owner][$test]['jnxPingLastTestResultProbeResponses'] ?? null,
                        'ProbeLoss' => (int) ($data[$owner][$test]['jnxPingLastTestResultSentProbes'] ?? 0) - (int) ($data[$owner][$test]['jnxPingLastTestResultProbeResponses'] ?? 0),
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('MinRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('MaxRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('StdDevRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    app('Datastore')->put($device, 'sla', $tags, $icmp);
                    $collected = array_merge($collected, $icmp);
                    break;
                case 'NtpQuery':
                case 'UdpTimestamp':
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla->sla_nr . ":\n");
            d_echo($collected);
        }
    }

    /**
     * Retrieve specific Juniper PingCtlType
     */
    private function retrieveJuniperType($rtt_type)
    {
        switch ($rtt_type) {
            case 'enterprises.2636.3.7.2.1':
                return 'IcmpTimeStamp';
            case 'enterprises.2636.3.7.2.2':
                return 'HttpGet';
            case 'enterprises.2636.3.7.2.3':
                return 'HttpGetMetadata';
            case 'enterprises.2636.3.7.2.4':
                return 'DnsQuery';
            case 'enterprises.2636.3.7.2.5':
                return 'NtpQuery';
            case 'enterprises.2636.3.7.2.6':
                return 'UdpTimestamp';
            case 'zeroDotZero':
                return 'twamp';
            default:
                return str_replace('ping', '', $rtt_type);
        }
    }

    /**
     * Parse type into a nicer name
     * jnxChassisEX4300.0 > EX4300
     * jnxEX4300SlotPower.0 > Slot Power
     * jnxEX4300MPSlotFan.0 > MP Slot Fan
     * jnxEX4300MPSlotFPC.0 > MP Slot FPC
     * jnxEX4300MediaCardSpacePIC.0 > Media Card Space PIC
     * jnxEX4300MPRE0.0 > MPRE0
     */
    public function parseType(?string $type, ?string $chassisName): ?string
    {
        if ($type === null) {
            return $type;
        }

        if (preg_match('/jnxChassis([^.]+).*/', $type, $matches)) {
            return $matches[1];
        }

        // $chassisName is known
        $name = preg_replace("/jnx($chassisName)?([^.]+).*/", '$2', $type);
        $words = preg_split('/(^[^A-Z]+|[A-Z][^A-Z0-9]+)/', $name, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        return implode(' ', $words);
    }

    public function parseClass($type): ?string
    {
        return match ($type) {
            'jnxFan' => 'fan',
            'jnxPower' => 'powerSupply',

            default => null,
        };
    }

    public function discoverTransceivers(): Collection
    {
        $entPhysical = SnmpQuery::walk('ENTITY-MIB::entityPhysical')->table(1);

        $jnxDomCurrentTable = SnmpQuery::cache()->walk('JUNIPER-DOM-MIB::jnxDomCurrentTable')->mapTable(function ($data, $ifIndex) use ($entPhysical) {
            $ent = $this->findTransceiverEntityByPortName($entPhysical, PortCache::getNameFromIfIndex($ifIndex, $this->getDevice()));
            if (empty($ent)) {
                return null; // no module
            }

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'type' => $ent['ENTITY-MIB::entPhysicalName'] ?? null,
                'vendor' => $ent['ENTITY-MIB::entPhysicalMfgName'] ?? null,
                'model' => $ent['ENTITY-MIB::entPhysicalModelName'] ?? null,
                'revision' => $ent['ENTITY-MIB::entPhysicalHardwareRev'] ?? null,
                'serial' => $ent['ENTITY-MIB::entPhysicalSerialNum'] ?? null,
                'channels' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleLaneCount'] ?? 0,
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();

        if ($jnxDomCurrentTable->isNotEmpty()) {
            return $jnxDomCurrentTable;
        }

        // could use improvement by mapping JUNIPER-IFOPTICS-MIB::jnxOpticsConfigTable for a tiny bit more info
        return SnmpQuery::cache()->walk('JUNIPER-IFOPTICS-MIB::jnxOpticsPMCurrentTable')
            ->mapTable(function ($data, $ifIndex) {
                return new Transceiver([
                    'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex),
                    'index' => $ifIndex,
                    'entity_physical_index' => $ifIndex,
                ]);
            });
    }

    private function findTransceiverEntityByPortName(array $entPhysical, ?string $ifName): array
    {
        if (! $ifName) {
            return [];
        }

        // Regex to capture three digit-groups (FPC/PIC/Port) from the ifName
        // e.g., et-0/0/0, et-0/0/0:2.0, et-1/2/3:100
        if (! preg_match('#-(\d+)/(\d+)/(\d+)#', $ifName, $matches)) {
            // No match; bail out.
            return [];
        }

        // [0] is the full match, [1..3] are captures
        [, $fpc, $pic, $port] = $matches;

        // EVO result from QFX5130     - ENTITY-MIB::entPhysicalDescr[75] = QSFP56-DD-400GBASE-DR4 @ /Chassis[0]/Fpc[0]/Pic[0]/Port[0]
        // non-EVO result from QFX5120 - ENTITY-MIB::entPhysicalDescr[287] = QSFP28-100G-AOC-3M @ 0/0/1
        $expectedSuffixes = [
            // Short form, e.g. " @ 0/0/1"
            ' @ ' . $fpc . '/' . $pic . '/' . $port,

            // Chassis form, e.g. " @ /Chassis[0]/Fpc[0]/Pic[0]/Port[0]"
            ' @ /Chassis[0]/Fpc[' . $fpc . ']/Pic[' . $pic . ']/Port[' . $port . ']',
        ];

        // Check if any entity description ends with one of the expected suffixes
        foreach ($entPhysical as $entity) {
            if (! isset($entity['ENTITY-MIB::entPhysicalDescr'])) {
                continue;
            }

            $descr = $entity['ENTITY-MIB::entPhysicalDescr'];

            foreach ($expectedSuffixes as $suffix) {
                if (str_ends_with($descr, $suffix)) {
                    return $entity; // Found a match
                }
            }
        }

        // Nothing matched
        return [];
    }
}
