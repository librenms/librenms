<?php

/**
 * Run against the disposable test database configured by DB_TEST_*:
 * DBTEST=1 php tests/benchmarks/sync-models.php --rows=1000,10000,50000 --runs=3 --mode=both
 * Each sample is rolled back. Timings include reconciliation and inserts, but not fixture construction.
 */

use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Discovery\FdbTable;
use Monolog\Handler\NullHandler;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;

if (getenv('DBTEST') !== '1') {
    fwrite(STDERR, "Set DBTEST=1 and configure DB_TEST_* for a disposable test database.\n");
    exit(1);
}

foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'testing', 'APP_DEBUG' => 'false', 'DEBUGBAR_ENABLED' => 'false'] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'testing', 'logging.channels.stdout' => [
    'driver' => 'monolog', 'handler' => NullHandler::class,
]]);

$options = getopt('', ['rows:', 'runs:', 'mode:', 'dataset:']);
$sizes = array_map(intval(...), explode(',', $options['rows'] ?? '1000,10000,50000'));
$runs = (int) ($options['runs'] ?? 3);
$mode = $options['mode'] ?? 'both';
$dataset = $options['dataset'] ?? 'arp';
if ($runs < 1 || min($sizes) < 1 || ! in_array($mode, ['save', 'bulk', 'both']) || ! in_array($dataset, ['arp', 'fdb'])) {
    throw new InvalidArgumentException('Use positive rows/runs, mode save/bulk/both and dataset arp/fdb');
}

$syncer = new class
{
    use SyncsModels;

    /** @param Collection<int, Ipv4Mac> $models */
    public function run(Device $device, Collection $models, string $mode): void
    {
        if ($mode === 'bulk') {
            $this->syncModelsInBulk($device, 'macs', $models);
        } else {
            $this->syncModels($device, 'macs', $models);
        }
    }
};

$connection = DB::connection('testing');
$connection->disableQueryLog();
$stats = new class
{
    public bool $active = false;
    public int $inserts = 0;
    public int $maxBindings = 0;
};
$table = $dataset === 'arp' ? 'ipv4_mac' : 'ports_fdb';
DB::listen(function (QueryExecuted $query) use ($stats, $table): void {
    if ($stats->active && preg_match('/^insert into [`"]?' . $table . '\b/i', $query->sql)) {
        $stats->inserts++;
        $stats->maxBindings = max($stats->maxBindings, count($query->bindings));
    }
});
ModuleModelObserver::observe(Ipv4Mac::class);
echo json_encode(['php' => PHP_VERSION, 'database' => $connection->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION),
    'emulated_prepares' => $connection->getPdo()->getAttribute(PDO::ATTR_EMULATE_PREPARES),
    'debugbar_enabled' => config('debugbar.enabled'), 'dataset' => $dataset, 'runs' => $runs]) . PHP_EOL;

foreach ($sizes as $size) {
    foreach ($mode === 'both' ? ['save', 'bulk'] : [$mode] as $strategy) {
        $samples = [];
        for ($run = 0; $run < $runs; $run++) {
            if ($app->bound(QueryRecorder::class)) {
                $app->make(QueryRecorder::class)->reset();
            }
            gc_collect_cycles();
            $connection->beginTransaction();
            try {
                $device = Device::factory()->create();
                $models = new Collection;
                $fdb = [];
                for ($i = 1; $i <= $size; $i++) {
                    if ($dataset === 'arp') {
                        $models->push(new Ipv4Mac(['port_id' => 1, 'ipv4_address' => long2ip(0x0a000000 + $i),
                            'mac_address' => sprintf('%012x', $i), 'context_name' => '']));
                    } else {
                        $fdb[1][sprintf('%012x', $i)] = ['port_id' => 1];
                    }
                }
                $stats->inserts = $stats->maxBindings = 0;
                $stats->active = true;
                if ($app->bound(QueryRecorder::class)) {
                    $app->make(QueryRecorder::class)->reset();
                }
                memory_reset_peak_usage();
                $start = hrtime(true);
                if ($dataset === 'arp') {
                    $syncer->run($device, $models, $strategy);
                } elseif ($strategy === 'bulk') {
                    FdbTable::sync($device->device_id, $fdb, [], []);
                } else {
                    // The old FDB path used one raw INSERT per newly discovered MAC.
                    $now = Carbon\Carbon::now();
                    foreach ($fdb[1] as $mac => $entry) {
                        $connection->table('ports_fdb')->insert(['device_id' => $device->device_id, 'port_id' => $entry['port_id'],
                            'vlan_id' => 1, 'mac_address' => $mac, 'created_at' => $now, 'updated_at' => $now]);
                    }
                }
                $elapsed = (hrtime(true) - $start) / 1e9;
                if ($connection->table($table)->where('device_id', $device->device_id)->count() !== $size) {
                    throw new RuntimeException('Inserted row count differs from input');
                }
                $samples[] = ['seconds' => $elapsed, 'peak_memory_mib' => memory_get_peak_usage(true) / 1048576,
                    'peak_used_memory_mib' => memory_get_peak_usage(false) / 1048576,
                    'inserts' => $stats->inserts, 'max_bindings' => $stats->maxBindings];
                $stats->active = false;
            } finally {
                $connection->rollBack();
                unset($device, $models, $fdb);
            }
        }
        $times = array_column($samples, 'seconds');
        sort($times);
        echo json_encode(['rows' => $size, 'strategy' => $strategy, 'median_seconds' => $times[intdiv($runs, 2)],
            'samples' => $samples, 'peak_memory_mib' => max(array_column($samples, 'peak_memory_mib'))]) . PHP_EOL;
    }
}
