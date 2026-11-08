<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\MefInfo;
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;

class Mef implements Module
{
    use SyncsModels;
    use RequiresPollingMethod;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $mefs = $os->discoverMef();

        if ($mefs === null) {
            return;
        }

        ModuleModelObserver::observe(MefInfo::class);
        $this->syncModels($os->getDevice(), 'mefInfo', $mefs);
        ModuleModelObserver::done();
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $mef_links = $device->mefInfo()->get();

        if ($mef_links->isEmpty()) {
            return;
        }

        foreach ($os->pollMef($mef_links) as $mef) {
            $mefIdent = $mef->getOriginal('mefIdent');
            if ($mefIdent !== null) {
                foreach ($mef->getDirty() as $property => $value) {
                    Eventlog::log('MEF Link : ' . $mefIdent . ' (' . preg_replace('/^mef/', '', (string) $mef->getOriginal($property)) . ') -> ' . $value, $device->device_id);
                }
            }

            $mef->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->mefInfo()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->mefInfo()->delete();
    }

    /**
     * @return array{mefinfo: Collection<int, MefInfo>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type != 'discovery') {
            return null;
        }

        return [
            'mefinfo' => $device->mefInfo()->get()
                ->sort(fn (MefInfo $a, MefInfo $b) => $a->mefID <=> $b->mefID)
                ->values()
                ->makeHidden(['id', 'device_id']),
        ];
    }
}
