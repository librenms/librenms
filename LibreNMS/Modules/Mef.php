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

        $columns = [
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus',
        ];

        $response = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->enumStrings()->abortOnFailure()->walk($columns);

        if ($response->getExitCode() !== 0) {
            return;
        }

        $mef_list = $response->valuesByIndex();

        if ($mef_list === []) {
            // An empty result with abortOnFailure() is ambiguous: a clean empty walk
            // of the first column aborts the remaining walks, so it is not known
            // whether the table is truly empty. Only allow syncModels() to delete
            // stored rows once every remaining column has walked cleanly and
            // returned nothing.
            foreach (array_slice($columns, 1) as $column) {
                $remaining = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->enumStrings()->walk($column);

                if ($remaining->getExitCode() !== 0 || ! $remaining->isEmpty() || $remaining->stderr !== '') {
                    return;
                }
            }
        } elseif (collect($mef_list)->contains(fn (array $row) => array_diff($columns, array_keys($row)))) {
            // A partial table (a requested column missing from a non-empty result)
            // would overwrite stored values with nulls.
            return;
        }

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

        $response = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->enumStrings()->abortOnFailure()->walk([
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus',
        ]);

        if ($response->getExitCode() !== 0 || ! $response->isValid()) {
            return;
        }

        $current = $response->valuesByIndex();

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

            $mefIdent = $mef->mefIdent;

            foreach ($values as $property => $value) {
                if ($value != $mef->{$property}) {
                    if ($mefIdent !== null) {
                        Eventlog::log('MEF Link : ' . $mefIdent . ' (' . preg_replace('/^mef/', '', (string) $mef->{$property}) . ') -> ' . $value, $device->device_id);
                    }
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
                ->makeHidden(['id', 'device_id']),
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
