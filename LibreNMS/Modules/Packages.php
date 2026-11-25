<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Package;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\PackagesDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\RemovesMethodData;
use LibreNMS\OS;

/**
 * Collect the installed software packages of a device.
 */
class Packages implements Module, RemovesMethodData
{
    use SyncsModels;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * The unix agent reports richer package data (version, build, size) than the HOST-RESOURCES-MIB, so it is preferred.
     *
     * @return list<PollingMethodType>
     */
    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::UnixAgent, PollingMethodType::Snmp];
    }

    public function shouldDiscover(OS $os): bool
    {
        return $os->getMethodResults()->anyAvailable($this->enabledMethods($os->getDevice()));
    }

    /**
     * Packages come from one method: the most preferred enabled method that has packages for this device.
     * Its packages replace those of other methods, so a device using several methods has no duplicates.
     * If a method is down or times out, keep the existing inventory.
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        foreach ($this->enabledMethods($device) as $type) {
            if (! $os->getMethodResults()->isAvailable($type)) {
                break;
            }

            $packages = $os->dataSource($type, PackagesDiscovery::class)?->discoverPackages() ?? new Collection;
            if ($packages->isNotEmpty()) {
                ModuleModelObserver::observe(Package::class);
                $this->syncModelsFromMethod($device, 'packages', $type, $packages);

                Log::info("{$type->value}: {$packages->count()} packages");

                return;
            }

            // a timed-out fetch is incomplete, so do not fall back to the next method — keep the existing inventory
            if ($os->getMethodResults()->result($type)?->stat('timed_out')) {
                break;
            }
        }

        Log::info('No packages found, keeping existing packages');
    }

    public function shouldPoll(OS $os): bool
    {
        return $this->shouldDiscover($os);
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @return list<PollingMethodType>
     */
    private function enabledMethods(Device $device): array
    {
        return array_values(array_filter($this->pollingMethods($device), $device->polling()->isEnabled(...)));
    }

    public function dataExists(Device $device): bool
    {
        return $device->packages()->exists();
    }

    public function removeMethodData(Device $device, PollingMethodType $type): void
    {
        $device->packages()->where('polling_method', $type)->delete();
    }

    public function cleanup(Device $device): int
    {
        return $device->packages()->delete();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'packages' => $device->packages()->orderBy('name')->orderBy('version')->orderBy('build')
                ->get()->map->makeHidden(['pkg_id', 'device_id', 'polling_method']),
        ];
    }
}
