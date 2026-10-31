<?php

namespace LibreNMS\Polling\Providers\Snmp;

use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\OS;
use LibreNMS\Polling\Providers\MempoolsProvider;
use SnmpQuery;

/**
 * SNMP mempools come from the OS, which knows the MIBs of the device.
 */
final readonly class SnmpMempools implements MempoolsProvider
{
    public function __construct(
        private OS $os,
    ) {
    }

    public function discoverMempools(): Collection
    {
        return $this->os->discoverMempools();
    }

    public function pollMempools(EloquentCollection $mempools): void
    {
        if ($this->os instanceof MempoolsPolling) {
            $this->os->pollMempools($mempools);

            return;
        }

        $oids = $mempools->map->only(['mempool_perc_oid', 'mempool_used_oid', 'mempool_free_oid', 'mempool_total_oid'])
            ->flatten()->filter()->unique()->values()->all();

        if (empty($oids)) {
            return;
        }

        $data = SnmpQuery::numeric()->get($oids)->values();

        $mempools->each(function (Mempool $mempool) use ($data): void {
            $mempool->fillUsage(
                $data[(string) $mempool->mempool_used_oid] ?? null,
                $data[(string) $mempool->mempool_total_oid] ?? null,
                $data[(string) $mempool->mempool_free_oid] ?? null,
                $data[(string) $mempool->mempool_perc_oid] ?? null
            );
        });
    }
}
