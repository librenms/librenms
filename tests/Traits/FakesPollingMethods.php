<?php

namespace LibreNMS\Tests\Traits;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\OS;
use LibreNMS\OS\Linux;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\ProbeResult;

trait FakesPollingMethods
{
    /**
     * An OS for a new device with these polling methods enabled.
     *
     * @param  PollingMethodType[]  $types
     */
    private function makeOs(array $types): OS
    {
        $device = Device::factory()->create(['os' => 'linux']);
        foreach ($types as $type) {
            DevicePollingMethod::factory()->create([
                'device_id' => $device->device_id,
                'method_type' => $type,
                'affects_availability' => false,
            ]);
        }

        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        return OS::make($deviceArray);
    }

    /**
     * Replace a polling method with one that is available or not, and whose check fetched these stats.
     *
     * @param  class-string<PollingMethod<PollingMethodConfig>>  $class
     * @param  array<string, mixed>  $stats
     * @return PollingMethod<PollingMethodConfig>
     */
    private function fakeMethod(string $class, bool $available, array $stats = []): PollingMethod
    {
        $method = new class($available ? ProbeResult::success($stats) : ProbeResult::failure(), app($class)) extends PollingMethod
        {
            public function __construct(
                private readonly ProbeResult $result,
                private readonly PollingMethod $real,
            ) {
            }

            public function dataSourceClass(): ?string
            {
                return $this->real->dataSourceClass();
            }

            public function dataSource(PollingMethodConfig $config, ProbeResult $result): ?object
            {
                return $this->real->dataSource($config, $result);
            }

            public function probe(Device $device, PollingMethodConfig $config): ProbeResult
            {
                return $this->result;
            }

            public function definition(): UnixAgentDefinition
            {
                return new UnixAgentDefinition;
            }

            public function defaultAffectsAvailability(): bool
            {
                return false;
            }

            public function defaults(?Device $device = null): array
            {
                return [];
            }

            public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): UnixAgentConfig
            {
                return new UnixAgentConfig(port: 6556, timeout: 1);
            }
        };

        $this->app->instance($class, $method);

        return $method;
    }

    /**
     * Agent output with only the system memory.
     */
    private function agentMemOutput(int $totalKb, int $freeKb): string
    {
        return "<<<mem>>>\nMemTotal: $totalKb kB\nMemFree: $freeKb kB\n";
    }

    /**
     * An OS for the same device, whose SNMP data has one mempool of this type, half used, or none when the type is null.
     */
    private function withSnmpMempools(OS $os, ?string $type = 'hrstorage', string $index = '1'): OS
    {
        $device = $os->getDeviceArray();

        return new class($device, $type, $index) extends Linux
        {
            public function __construct(array &$device, private readonly ?string $type, private readonly string $index)
            {
                parent::__construct($device);
            }

            public function discoverMempools(): Collection
            {
                if ($this->type === null) {
                    return new Collection;
                }

                $mempool = new Mempool(['mempool_index' => $this->index, 'mempool_type' => $this->type, 'mempool_class' => 'system', 'mempool_descr' => "$this->type memory"]);

                return collect([$mempool->fillUsage(50, 100)]);
            }

            public function pollMempools(EloquentCollection $mempools): EloquentCollection
            {
                return $mempools->each(fn (Mempool $mempool) => $mempool->fillUsage(60, 100));
            }
        };
    }

    /**
     * Whether the method was checked during this poll or discovery.
     */
    private function wasChecked(OS $os, PollingMethodType $type): bool
    {
        return $os->getDevice()->pollingMethod($type)?->last_check_successful !== null;
    }
}
