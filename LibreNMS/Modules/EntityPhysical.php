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
        $data = \SnmpQuery::hideMib()->enumStrings()
            ->mibs(['CISCO-ENTITY-VENDORTYPE-OID-MIB'])
            ->walk('ENTITY-MIB::entPhysicalTable');

        if (! $data->isValid()) {
            return;
        }

        $mapping = \SnmpQuery::walk('ENTITY-MIB::entAliasMappingIdentifier')->table(2);

        $inventory = $data->mapTable(function ($data, $entityPhysicalIndex) use ($mapping) {
            $entityPhysical = new EntPhysical($data);
            $entityPhysical->entPhysicalIndex = $entityPhysicalIndex;

            // fill ifIndex
            if (isset($mapping[$entityPhysicalIndex][0]['ENTITY-MIB::entAliasMappingIdentifier'])) {
                if (preg_match('/IF-MIB::ifIndex\[(\d+)]/', $mapping[$entityPhysicalIndex][0]['ENTITY-MIB::entAliasMappingIdentifier'], $matches)) {
                    $entityPhysical->ifIndex = $matches[1];
                }
            }

            return $entityPhysical;
        });

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

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->entityPhysical()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'entPhysical' => $device->entityPhysical()->orderBy('entPhysicalIndex')
                ->get()->map->makeHidden(['device_id', 'entPhysical_id']),
        ];
    }
}
