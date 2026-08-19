<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
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
    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $inventory = $os->discoverEntityPhysical();

        // A failed SNMP walk (e.g. a timeout) yields an empty collection that is
        // indistinguishable from a device that genuinely has no entPhysical data.
        // Syncing that empty result would delete all existing inventory rows, so if
        // the device already has inventory we keep the last-known-good and warn
        // rather than wipe it on a transient collection failure.
        if ($inventory->isEmpty() && $os->getDevice()->entityPhysical()->exists()) {
            Log::warning('entPhysical discovery returned no data for ' . $os->getDevice()->hostname
                . '; keeping existing inventory (possible SNMP collection failure)');

            return;
        }

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
