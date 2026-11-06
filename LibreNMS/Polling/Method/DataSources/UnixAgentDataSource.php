<?php

namespace LibreNMS\Polling\Method\DataSources;

use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\Modules\UnixAgent;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * Module data from the agent output. The check fetched the whole output, so this reads it instead of querying the device.
 */
final readonly class UnixAgentDataSource implements MempoolsDiscovery, MempoolsPolling
{
    /**
     * The meminfo fields of each mempool, keyed by mempool_index. Laid out like the UCD-SNMP-MIB mempools.
     */
    private const MEMPOOLS = [
        1 => ['class' => 'system', 'descr' => 'Physical memory', 'total' => 'MemTotal', 'free' => 'MemFree'],
        2 => ['class' => 'swap', 'descr' => 'Swap space', 'total' => 'SwapTotal', 'free' => 'SwapFree'],
        3 => ['class' => 'buffers', 'descr' => 'Memory buffers', 'total' => 'MemTotal', 'used' => 'Buffers'],
        4 => ['class' => 'cached', 'descr' => 'Cached memory', 'total' => 'MemTotal', 'used' => 'Cached'],
        5 => ['class' => 'available', 'descr' => 'Available memory', 'total' => 'MemTotal', 'free' => 'MemAvailable'],
    ];

    /**
     * Values of the <<<mem>>> section in kB, keyed by field. Empty when the agent did not send it.
     *
     * @var array<string, int>
     */
    private array $meminfo;

    public function __construct(ProbeResult $result)
    {
        $section = UnixAgent::parse((string) $result->stat('output'))['mem'] ?? null;

        $meminfo = [];
        foreach (is_string($section) ? explode("\n", $section) : [] as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                $meminfo[$matches[1]] = (int) $matches[2];
            }
        }

        $this->meminfo = $meminfo;
    }

    /**
     * @return Collection<int, Mempool>
     */
    public function discoverMempools(): Collection
    {
        $mempools = new Collection;

        foreach (self::MEMPOOLS as $index => $fields) {
            if (! isset($this->meminfo[$fields['used'] ?? $fields['free']])) {
                continue;
            }

            $mempools->push($this->fill(new Mempool([
                'mempool_index' => $index,
                'mempool_type' => 'unix-agent',
                'mempool_class' => $fields['class'],
                'mempool_precision' => 1024,
                'mempool_descr' => $fields['descr'],
            ])));
        }

        return $mempools;
    }

    /**
     * None are polled when the agent did not send the section.
     *
     * @param  EloquentCollection<int, Mempool>  $mempools
     * @return EloquentCollection<int, Mempool>
     */
    public function pollMempools(EloquentCollection $mempools): EloquentCollection
    {
        return empty($this->meminfo) ? new EloquentCollection : $mempools->each($this->fill(...));
    }

    private function fill(Mempool $mempool): Mempool
    {
        $fields = self::MEMPOOLS[(int) $mempool->mempool_index] ?? null;

        if ($fields === null) {
            return $mempool;
        }

        return $mempool->fillUsage(
            isset($fields['used']) ? ($this->meminfo[$fields['used']] ?? null) : null,
            $this->meminfo[$fields['total']] ?? null,
            isset($fields['free']) ? ($this->meminfo[$fields['free']] ?? null) : null,
        );
    }
}
