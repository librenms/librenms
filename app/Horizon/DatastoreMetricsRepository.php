<?php

namespace App\Horizon;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use Illuminate\Support\Str;
use Laravel\Horizon\Repositories\RedisMetricsRepository;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;

/**
 * Horizon keeps its metrics in redis for the dashboard and auto scaling.
 * Each snapshot is also written to the LibreNMS datastores for long term graphs.
 */
class DatastoreMetricsRepository extends RedisMetricsRepository
{
    /** rrd folder, underscore so it cannot collide with a device hostname */
    public const HOSTNAME = '_horizon';

    /** @var array<string, array{throughput: mixed, runtime: mixed}> */
    private array $snapshotData = [];

    public function snapshot()
    {
        $this->snapshotData = [];

        parent::snapshot();

        $this->writeToDatastore($this->snapshotData);
    }

    protected function baseSnapshotData($key)
    {
        return $this->snapshotData[$key] = parent::baseSnapshotData($key);
    }

    /**
     * @param  array<string, array{throughput: mixed, runtime: mixed}>  $snapshotData  keyed by job:<name> or queue:<name>
     */
    public function writeToDatastore(array $snapshotData): void
    {
        if (LibrenmsConfig::get('rrd.enable', true)) {
            Rrd::checkDirExists(RrdPath::make(self::HOSTNAME));
        }

        $meta = [
            'device' => new Device(['hostname' => self::HOSTNAME]),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('throughput', 'GAUGE', 0)
                ->addDataset('runtime', 'GAUGE', 0),
        ];

        foreach (self::aggregate($snapshotData) as $type => $metrics) {
            foreach ($metrics as $name => $fields) {
                app('Datastore')->write('horizon', $fields, ['type' => $type, 'name' => $name], [
                    ...$meta,
                    'rrd_name' => ['horizon', $type, $name],
                ]);
            }
        }
    }

    /**
     * Combine jobs by class, job display names may include an id (PollDevice:12).
     * Runtime is the average in milliseconds, null when nothing ran.
     *
     * @param  array<string, array{throughput: mixed, runtime: mixed}>  $snapshotData
     * @return array<string, array<string, array{throughput: int, runtime: float|null}>>
     */
    public static function aggregate(array $snapshotData): array
    {
        $totals = [];

        foreach ($snapshotData as $key => $data) {
            [$type, $name] = explode(':', $key, 2) + [1 => ''];
            if ($type === 'job') {
                $name = class_basename(Str::before($name, ':'));
            }

            $throughput = (int) $data['throughput'];
            $totals[$type][$name]['throughput'] = ($totals[$type][$name]['throughput'] ?? 0) + $throughput;
            $totals[$type][$name]['time'] = ($totals[$type][$name]['time'] ?? 0) + $throughput * (float) $data['runtime'];
        }

        return array_map(fn (array $metrics): array => array_map(fn (array $total): array => [
            'throughput' => $total['throughput'],
            'runtime' => $total['throughput'] ? $total['time'] / $total['throughput'] : null,
        ], $metrics), $totals);
    }
}
