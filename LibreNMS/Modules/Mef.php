<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\MefInfo;
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use SnmpQuery;

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
        $device = $os->getDevice();

        $mef_list = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->walk([
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus',
        ])->valuesByIndex();

        $models = collect($mef_list)->map(fn (array $entry, int $mefID) => new MefInfo([
            'device_id' => $device->device_id,
            'mefID' => $mefID,
            'mefType' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType'],
            'mefIdent' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier'],
            'mefMTU' => $this->correctMtu($entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize'], $device),
            'mefAdmState' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState'],
            'mefRowState' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus'],
        ]))->values();

        ModuleModelObserver::observe(MefInfo::class);
        $this->syncModels($device, 'mefInfo', $models);
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

        $current = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->walk([
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus',
        ])->valuesByIndex();

        foreach ($mef_links as $mef) {
            $snmp = $current[$mef->mefID] ?? null;
            if ($snmp === null) {
                Log::debug("MEF link {$mef->mefIdent} ({$mef->mefID}) no longer reported, skipping");

                continue;
            }

            $values = [
                'mefType' => $snmp['MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType'],
                'mefIdent' => $snmp['MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier'],
                'mefMTU' => $this->correctMtu($snmp['MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize'], $device),
                'mefAdmState' => $snmp['MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState'],
                'mefRowState' => $snmp['MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus'],
            ];

            foreach ($values as $property => $value) {
                if ($value != $mef->{$property}) {
                    Eventlog::log('MEF Link : ' . $mef->mefIdent . ' (' . preg_replace('/^mef/', '', (string) $mef->{$property}) . ') -> ' . $value, $device->device_id);
                    $mef->{$property} = $value;
                }
            }

            if ($mef->isDirty()) {
                $mef->save();
            }
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
                ->makeHidden(['id']),
        ];
    }

    /**
     * Coriant MEF-EVC is a bit strange: the MTU is sometimes set to 0
     *
     * @param  int  $mtu
     */
    private function correctMtu($mtu, Device $device): int
    {
        if ($mtu == 0 && $device->os === 'coriant') {
            return 1600;
        }

        return (int) $mtu;
    }
}
