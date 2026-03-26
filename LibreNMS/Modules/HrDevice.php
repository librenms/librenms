<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class HrDevice implements Module
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
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $models = \SnmpQuery::mibs(['HOST-RESOURCES-TYPES'])
            ->enumStrings()
            ->hideMib()
            ->walk([
                'HOST-RESOURCES-MIB::hrProcessorLoad',
                'HOST-RESOURCES-MIB::hrDeviceTable',
            ])->mapTable(function (array $hrDevice, int $hrDeviceIndex) {
                $model = new \App\Models\HrDevice($hrDevice);
                $model->hrDeviceIndex = $hrDeviceIndex;

                return $model;
            });

        ModuleModelObserver::observe(\App\Models\HrDevice::class);
        $this->syncModels($os->getDevice(), 'hostResources', $models);
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
    public function dataExists(Device $device): bool
    {
        return $device->hostResources()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->hostResources()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'poller') {
            return null;
        }

        return [
            'hrDevice' => $device->hostResources()
                ->orderBy('hrDeviceIndex')
                ->get()->map->makeHidden(['hrDevice_id', 'device_id']),
        ];
    }
}
