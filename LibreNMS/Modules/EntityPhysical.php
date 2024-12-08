<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class EntityPhysical implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $inventory = $os->discoverEntityPhysical();

        ModuleModelObserver::observe(EntPhysical::class);
        $this->syncModels($os->getDevice(), 'entityPhysical', $inventory);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    public function dataExists(Device $device): bool
    {
        return $device->entityPhysical()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->entityPhysical()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'entPhysical' => $device->entityPhysical()->orderBy('entPhysicalIndex')
                ->get()->map->makeHidden(['device_id', 'entPhysical_id']),
        ];
    }
}
