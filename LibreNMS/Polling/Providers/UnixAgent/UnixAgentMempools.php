<?php

namespace LibreNMS\Polling\Providers\UnixAgent;

use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Modules\UnixAgent;
use LibreNMS\Polling\Providers\MempoolsProvider;

/**
 * Mempools from the <<<mem>>> section of the agent, the contents of /proc/meminfo.
 * The check fetched the whole agent output, so this reads it instead of querying the device.
 */
final readonly class UnixAgentMempools implements MempoolsProvider
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
     * @param  array<string, int>  $meminfo  values in kB, keyed by field
     */
    public function __construct(
        private array $meminfo,
    ) {
    }

    /**
     * Null when the agent did not send a <<<mem>>> section.
     */
    public static function fromOutput(string $output): ?self
    {
        $section = UnixAgent::parse($output)['mem'] ?? null;

        if (! is_string($section) || $section === '') {
            return null;
        }

        $meminfo = [];
        foreach (explode("\n", $section) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                $meminfo[$matches[1]] = (int) $matches[2];
            }
        }

        return empty($meminfo) ? null : new self($meminfo);
    }

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

    public function pollMempools(EloquentCollection $mempools): void
    {
        $mempools->each($this->fill(...));
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
