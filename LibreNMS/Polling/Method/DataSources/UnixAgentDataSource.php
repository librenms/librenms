<?php

namespace LibreNMS\Polling\Method\DataSources;

use App\Models\Mempool;
use App\Models\Package;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\PackagesDiscovery;
use LibreNMS\Interfaces\Discovery\SensorsDiscovery;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\Interfaces\Polling\SensorsPolling;
use LibreNMS\Modules\UnixAgent;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Number;

/**
 * Module data from the agent output. The check fetched the whole output, so this reads it instead of querying the device.
 */
final readonly class UnixAgentDataSource implements MempoolsDiscovery, MempoolsPolling, PackagesDiscovery, SensorsDiscovery, SensorsPolling
{
    /**
     * The meminfo fields of each mempool, keyed by mempool_index. Laid out like the UCD-SNMP-MIB mempools.
     */
    private const array MEMPOOLS = [
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

    /**
     * The installed packages parsed from the agent output. Empty when the agent sent no package section.
     *
     * @var Collection<int, Package>
     */
    private Collection $packages;

    /**
     * Disks of the <<<hddtemp>>> section, keyed by sensor_index (the position of the disk, from 1).
     *
     * @var array<int, array{device: string, model: string, temperature: ?float}>
     */
    private array $disks;

    public function __construct(ProbeResult $result)
    {
        $agent_data = UnixAgent::parse((string) $result->stat('output'));

        $meminfo = [];
        $section = $agent_data['mem'] ?? null;
        foreach (is_string($section) ? explode("\n", $section) : [] as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                $meminfo[$matches[1]] = (int) $matches[2];
            }
        }

        $this->meminfo = $meminfo;

        // a timed-out fetch truncates the package list, so a partial output would delete the
        // packages it did not reach; keep the existing set until a complete fetch succeeds
        $this->packages = $result->stat('timed_out') ? new Collection : $this->parsePackages($agent_data);
        $this->disks = self::parseHddtemp(is_string($agent_data['hddtemp'] ?? null) ? $agent_data['hddtemp'] : '');
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

    /**
     * @return Collection<int, Package>
     */
    public function discoverPackages(): Collection
    {
        return $this->packages;
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

    /**
     * The package sections the agent supports, one per device. Only the first present section is used.
     *
     * @param  array<string, string|array<string, string>>  $agent_data
     * @return Collection<int, Package>
     */
    private function parsePackages(array $agent_data): Collection
    {
        foreach (['rpm', 'dpkg', 'pacman'] as $manager) {
            if (! empty($agent_data[$manager])) {
                return $this->packagesFrom($manager, (string) $agent_data[$manager]);
            }
        }

        return new Collection;
    }

    /**
     * @return Collection<int, Package>
     */
    private function packagesFrom(string $manager, string $data): Collection
    {
        $packages = new Collection;

        foreach (explode("\n", $data) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($package = $this->parsePackageLine($manager, $line)) {
                $packages->push($package);
            }
        }

        return $packages;
    }

    private function parsePackageLine(string $manager, string $line): ?Package
    {
        return match ($manager) {
            'rpm' => $this->rpmPackage($line),
            'dpkg' => $this->debPackage($line),
            'pacman' => $this->pacmanPackage($line),
            default => null,
        };
    }

    private function rpmPackage(string $line): ?Package
    {
        [$name, $version, $build, $arch, $size] = array_pad(explode(' ', $line), 5, '');

        return $this->package('rpm', $name, $version, $build, $arch, $size);
    }

    private function debPackage(string $line): ?Package
    {
        [$name, $version, $arch, $size] = array_pad(explode(' ', $line), 4, '');

        return $this->package('deb', $name, $version, '', $arch, Number::cast($size) * 1024);
    }

    private function pacmanPackage(string $line): ?Package
    {
        [$name, $version, $arch, $size] = array_pad(explode(' ', $line), 4, '');

        return $this->package('pacman', $name, $version, '', $arch, (int) Number::toBytes($size));
    }

    private function package(string $manager, string $name, string $version, string $build, string $arch, mixed $size): ?Package
    {
        $package = new Package([
            'manager' => $manager,
            'name' => $name,
            'arch' => $arch,
            'version' => $version,
            'build' => $build,
            'size' => $size,
        ]);

        return $package->isValid() ? $package : null;
    }

    /**
     * Disk temperatures from hddtemp, disks that report no temperature (sleeping) have no value
     *
     * @return Collection<int, Sensor>
     */
    public function discoverSensors(): Collection
    {
        $sensors = new Collection;

        foreach ($this->disks as $index => $disk) {
            $sensors->push(new Sensor([
                'poller_type' => 'agent',
                'sensor_class' => 'temperature',
                'sensor_oid' => '',
                'sensor_index' => $index,
                'sensor_type' => 'hddtemp',
                'sensor_descr' => "{$disk['device']}: {$disk['model']}",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $disk['temperature'],
                'rrd_type' => 'GAUGE',
            ]));
        }

        return $sensors;
    }

    /**
     * Disks without a temperature are not polled
     *
     * @param  EloquentCollection<int, Sensor>  $sensors
     * @return array<int, float>
     */
    public function pollSensors(EloquentCollection $sensors): array
    {
        $values = [];

        foreach ($sensors->where('sensor_type', 'hddtemp') as $sensor) {
            $temperature = $this->disks[(int) $sensor->sensor_index]['temperature'] ?? null;
            if ($temperature !== null) {
                $values[$sensor->sensor_id] = $temperature;
            }
        }

        return $values;
    }

    /**
     * hddtemp format: |/dev/sda|WDC WD1003FBYX|35|C||/dev/sdb|...|
     *
     * @return array<int, array{device: string, model: string, temperature: ?float}>
     */
    private static function parseHddtemp(string $hddtemp): array
    {
        $hddtemp = trim($hddtemp, '|');
        if ($hddtemp === '') {
            return [];
        }

        $disks = [];
        foreach (explode('||', $hddtemp) as $position => $disk) {
            [$device, $model, $temperature] = array_pad(explode('|', $disk, 4), 3, '');
            $temperature = trim(str_replace('C', '', $temperature));

            // no temperature: SLP (sleeping), NA, ERR
            $disks[$position + 1] = [
                'device' => $device,
                'model' => $model,
                'temperature' => is_numeric($temperature) ? (float) $temperature : null,
            ];
        }

        return $disks;
    }
}
