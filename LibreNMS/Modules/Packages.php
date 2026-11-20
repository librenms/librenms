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
        return false; // the package list is kept fresh while polling
    }

    public function discover(OS $os): void
    {
        // no discovery
    }

    /**
     * The package list is collected while polling, so run when one of the methods is available.
     */
    public function shouldPoll(OS $os): bool
    {
        return $os->getMethodResults()->anyAvailable($this->enabledMethods($os->getDevice()));
    }

    /**
     * Packages come from one method: the most preferred enabled method that has packages for this device.
     * Its packages replace those of other methods, so a device polled by several methods has no duplicates.
     * Methods after one that is down are not tried, so packages do not move to another method while it is down.
     * When no method has packages, the existing ones are kept, so an incomplete agent output does not remove them.
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
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
        }

        Log::info('No packages found, keeping existing packages');
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
        if ($type === 'discovery') {
            return null;
        }

        return [
            'packages' => $device->packages()->orderBy('name')->orderBy('version')->orderBy('build')
                ->get()->map->makeHidden(['pkg_id', 'device_id', 'polling_method']),
        ];
    }
}
