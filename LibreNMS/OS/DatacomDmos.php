<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Facades\PortCache;
use App\Models\Mempool;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class DatacomDmos extends OS implements MempoolsDiscovery, ProcessorDiscovery, TransceiverDiscovery
{
    private const CPU_OID = '.1.3.6.1.4.1.3709.3.6.4.1.2.1.4';
    private const MEM_TOTAL_OID = '.1.3.6.1.4.1.3709.3.6.4.2.2.1.26';
    private const MEM_USED_OID = '.1.3.6.1.4.1.3709.3.6.4.2.2.1.27';
    private const MEM_FREE_OID = '.1.3.6.1.4.1.3709.3.6.4.2.2.1.28';

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device);

        $module = $this->findPrimaryModule();
        if ($module === null) {
            return;
        }

        if (empty($device->features) && ! empty($module['name'])) {
            $device->features = $module['name'];
        }

        if (empty($device->serial) && ! empty($module['serial'])) {
            $device->serial = $module['serial'];
        }
    }

    public function discoverProcessors()
    {
        $processors = [];
        foreach ($this->walkNumeric(self::CPU_OID) as $oid => $value) {
            $index = $this->extractValidatedIndex((string) $oid, self::CPU_OID);
            if ($index === null) {
                continue;
            }

            $processors[] = Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                $oid,
                $index,
                "Processor $index",
                1,
                is_numeric($value) ? (int) round((float) $value) : null
            );
        }

        return ! empty($processors) ? $processors : parent::discoverProcessors();
    }

    public function discoverMempools()
    {
        $totals = $this->walkNumeric(self::MEM_TOTAL_OID);
        $used = $this->walkNumeric(self::MEM_USED_OID);
        $free = $this->walkNumeric(self::MEM_FREE_OID);

        $mempools = new Collection();
        $found = false;
        foreach ($totals as $oid => $total) {
            $index = $this->extractValidatedIndex((string) $oid, self::MEM_TOTAL_OID);
            if ($index === null) {
                continue;
            }

            $usedOid = self::MEM_USED_OID . '.' . $index;
            $freeOid = self::MEM_FREE_OID . '.' . $index;
            $totalValue = is_numeric($total) ? (float) $total : null;
            $usedValue = isset($used[$usedOid]) && is_numeric($used[$usedOid]) ? (float) $used[$usedOid] : null;
            $freeValue = isset($free[$freeOid]) && is_numeric($free[$freeOid]) ? (float) $free[$freeOid] : null;

            $mempool = new Mempool([
                'mempool_index' => $index,
                'mempool_type' => 'datacom-dmos',
                'mempool_class' => 'system',
                'mempool_precision' => 1,
                'mempool_descr' => 'Memory',
                'mempool_total_oid' => self::MEM_TOTAL_OID . '.' . $index,
                'mempool_used_oid' => $usedOid,
                'mempool_free_oid' => $freeOid,
            ]);

            $mempool->fillUsage($usedValue, $totalValue, $freeValue);
            $mempools->push($mempool);
            $found = true;
        }

        return $found ? $mempools : parent::discoverMempools();
    }

    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::cache()
            ->mibDir('datacom')
            ->mibs(['DMOS-TRANSCEIVER-MIB'])
            ->hideMib()
            ->walk('transceiverTable')
            ->mapTable(function ($data, $ifIndex) {
                $portId = (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
                if ($portId <= 0) {
                    return null;
                }

                $laneCount = (int) ($data['laneCount'] ?? 0);

                return new Transceiver([
                    'port_id' => $portId,
                    'index' => (string) $ifIndex,
                    'entity_physical_index' => (string) $ifIndex,
                    'type' => 'SFP',
                    'ddm' => true,
                    'channels' => $laneCount > 0 ? $laneCount : null,
                ]);
            })
            ->filter();
    }

    /**
     * @return array<string, string>
     */
    private function walkNumeric(string $oid): array
    {
        return SnmpQuery::numeric()->walk($oid)->values();
    }

    /**
     * @return array{name: string, serial: string}|null
     */
    private function findPrimaryModule(): ?array
    {
        $entities = SnmpQuery::cache()
            ->mibs(['ENTITY-MIB'])
            ->hideMib()
            ->walk([
                'ENTITY-MIB::entPhysicalClass',
                'ENTITY-MIB::entPhysicalName',
                'ENTITY-MIB::entPhysicalSerialNum',
            ])->table(1);

        foreach ($entities as $entity) {
            $class = (string) ($entity['entPhysicalClass'] ?? '');
            $name = trim((string) ($entity['entPhysicalName'] ?? ''));
            $serial = trim((string) ($entity['entPhysicalSerialNum'] ?? ''));

            if ($class !== 'module' && $class !== '9') {
                continue;
            }

            if ($name === '' || preg_match('/^(slot|psu)/i', $name)) {
                continue;
            }

            if (preg_match('/port/i', $name)) {
                continue;
            }

            return [
                'name' => $name,
                'serial' => $serial,
            ];
        }

        return null;
    }

    private function extractValidatedIndex(string $oid, string $baseOid): ?string
    {
        $index = ltrim(substr($oid, strlen($baseOid)), '.');
        if ($index === '') {
            return null;
        }

        // DMOS samples use dotted numeric indexes like 1.1.49.
        return preg_match('/^\d+(?:\.\d+)+$/', $index) ? $index : null;
    }
}
