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
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Providers\MempoolsProvider;

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
     * Replace a polling method with one that is available or not, and provides these mempools.
     *
     * @param  class-string<PollingMethod<PollingMethodConfig>>  $class
     * @return PollingMethod<PollingMethodConfig>
     */
    private function fakeMethod(string $class, bool $available, ?MempoolsProvider $mempools = null): PollingMethod
    {
        $method = new class($available, $mempools) extends PollingMethod
        {
            public function __construct(
                private readonly bool $available,
                private readonly ?MempoolsProvider $mempools,
            ) {
            }

            public function probe(Device $device, PollingMethodConfig $config): ProbeResult
            {
                return $this->available ? ProbeResult::success() : ProbeResult::failure();
            }

            protected function providers(): array
            {
                return [MempoolsProvider::class => fn () => $this->mempools];
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
     * Provides one mempool, half used.
     */
    private function fakeMempools(string $type, string $index = '1'): MempoolsProvider
    {
        $mempool = new Mempool(['mempool_index' => $index, 'mempool_type' => $type, 'mempool_class' => 'system', 'mempool_descr' => "$type memory"]);

        return new class(collect([$mempool])) implements MempoolsProvider
        {
            /**
             * @param  Collection<int, Mempool>  $mempools
             */
            public function __construct(private readonly Collection $mempools)
            {
            }

            public function discoverMempools(): Collection
            {
                return $this->mempools->map(fn (Mempool $mempool) => (clone $mempool)->fillUsage(50, 100));
            }

            public function pollMempools(EloquentCollection $mempools): void
            {
                $mempools->each(fn (Mempool $mempool) => $mempool->fillUsage(60, 100));
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
