<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\MefInfo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\ProgressList;
use SnmpQuery;

class Mef implements Module
{
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

        $mef_list = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->hideMib()->cache()->walk('MefServiceEvcCfgEntry')->valuesByIndex();

        $progress = ProgressList::start('MEF');
        foreach ($mef_list as $index => $entry) {
            if (! $device->mefInfo()->where('mefID', (int) $index)->exists()) {
                MefInfo::create([
                    'device_id' => $device->device_id,
                    'mefID' => (int) $index,
                    'mefType' => $entry['mefServiceEvcCfgServiceType'],
                    'mefIdent' => $entry['mefServiceEvcCfgIdentifier'],
                    'mefMTU' => $this->correctMtu($entry['mefServiceEvcCfgMtuSize'], $device),
                    'mefAdmState' => $entry['mefServiceEvcCfgAdminState'],
                    'mefRowState' => $entry['mefServiceEvcCfgRowStatus'],
                ]);
                Eventlog::log('MEF link: ' . $entry['mefServiceEvcCfgIdentifier'] . " ($index) Discovered", $device->device_id, 'system', Severity::Info);
                $progress->mark('+');
            } else {
                $progress->mark('.');
            }
        }
        $progress->end();

        // Delete MEF links which were removed from the device
        $device->mefInfo()
            ->whereNotIn('mefID', array_map(intval(...), array_keys($mef_list)))
            ->get()
            ->each(function (MefInfo $mef) use ($device): void {
                Eventlog::log('MEF link: ' . $mef->mefIdent . ' Removed', $device->device_id, 'system', Severity::Notice);
                $mef->delete();
            });
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

        $current = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->hideMib()->cache()->walk('MefServiceEvcCfgEntry')->valuesByIndex();

        foreach ($mef_links as $mef) {
            $snmp = $current[$mef->mefID] ?? null;
            if ($snmp === null) {
                Log::debug("MEF link {$mef->mefIdent} ({$mef->mefID}) no longer reported, skipping");

                continue;
            }

            $values = [
                'mefType' => $snmp['mefServiceEvcCfgServiceType'],
                'mefIdent' => $snmp['mefServiceEvcCfgIdentifier'],
                'mefMTU' => $this->correctMtu($snmp['mefServiceEvcCfgMtuSize'], $device),
                'mefAdmState' => $snmp['mefServiceEvcCfgAdminState'],
                'mefRowState' => $snmp['mefServiceEvcCfgRowStatus'],
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
     */
    private function correctMtu($mtu, Device $device): int
    {
        if ($mtu == 0 && $device->os === 'coriant') {
            return 1600;
        }

        return (int) $mtu;
    }
}
