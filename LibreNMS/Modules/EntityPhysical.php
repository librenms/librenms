<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\StringHelpers;

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

        // Some devices return ENTITY-MIB strings in other encodings or with invalid
        // bytes, which would crash the database write on utf8mb4 columns in strict
        // mode. Convert the string fields using the existing encoding helper. See #20361
        $stringFields = [
            'entPhysicalDescr',
            'entPhysicalName',
            'entPhysicalHardwareRev',
            'entPhysicalFirmwareRev',
            'entPhysicalSoftwareRev',
            'entPhysicalSerialNum',
            'entPhysicalMfgName',
            'entPhysicalModelName',
            'entPhysicalAlias',
            'entPhysicalAssetID',
        ];
        $inventory->each(function (EntPhysical $entityPhysical) use ($stringFields): void {
            foreach ($stringFields as $field) {
                $value = $entityPhysical->getAttribute($field);
                if (is_string($value)) {
                    $clean = StringHelpers::inferEncoding($value);
                    if (is_string($clean) && $clean !== $value) {
                        $entityPhysical->setRawAttributes([$field => $clean], true);
                    }
                }
            }
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
