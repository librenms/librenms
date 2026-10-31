<?php

namespace LibreNMS\Modules;

use App\Models\CefSwitching;
use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\ProgressList;
use SnmpQuery;

class CiscoCef implements Module
{
    use RequiresPollingMethod;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        // cefSwitching rows are keyed by entPhysicalIndex, populated by the entity-physical module
        return ['entity-physical'];
    }

    /**
     * @inheritDoc
     */
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

        $cefs = SnmpQuery::hideMib()->walk('CISCO-CEF-MIB::cefSwitchingPath')->table(3);
        if (empty($cefs)) {
            return;
        }

        $entities = $this->entityNames($device);

        $discovered = [];
        $progress = ProgressList::start('CISCO CEF');
        foreach ($cefs as $entity => $afis) {
            $entity_name = $this->entityName($entities, $entity);
            Log::info("\n$entity $entity_name");
            foreach ($afis as $afi => $paths) {
                Log::info(" |- $afi");
                foreach ($paths as $path => $path_name) {
                    Log::info(' | |-' . $path . ': ' . $path_name['cefSwitchingPath']);
                    $discovered[$this->cefKey($device->device_id, $entity, $afi, $path)] = true;

                    if (! $device->cefSwitching()
                        ->where('entPhysicalIndex', (int) $entity)
                        ->where('afi', $afi)
                        ->where('cef_index', (int) $path)
                        ->exists()) {
                        CefSwitching::create([
                            'device_id' => $device->device_id,
                            'entPhysicalIndex' => (int) $entity,
                            'afi' => $afi,
                            'cef_index' => (int) $path,
                            'cef_path' => $path_name['cefSwitchingPath'],
                        ]);
                        Log::info('+', ['nlb' => true]);
                        $progress->mark('+');
                    } else {
                        $progress->mark('.');
                    }
                }
            }
        }
        $progress->end();

        $this->deleteStale($device, array_keys($discovered));
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        if ($os->getDeviceArray()['os_group'] !== 'cisco') {
            return;
        }

        $cefs = SnmpQuery::hideMib()->walk('CISCO-CEF-MIB::cefSwitchingStatsTable')->table(3);
        if (empty($cefs)) {
            return;
        }

        $entities = $this->entityNames($device);
        $polled = time();

        $existing = $device->cefSwitching()
            ->get()
            ->keyBy(fn (CefSwitching $cef) => $this->cefKey($device->device_id, $cef->entPhysicalIndex, $cef->afi, $cef->cef_index));

        $discovered = [];
        $progress = ProgressList::start('CISCO CEF');
        foreach ($cefs as $entity => $afis) {
            $entity_name = $this->entityName($entities, $entity);
            Log::info("\n$entity $entity_name");
            foreach ($afis as $afi => $paths) {
                Log::info(" |- $afi");
                foreach ($paths as $index => $stat) {
                    Log::info(' | |-' . $index . ': ' . ($stat['cefSwitchingPath'] ?? ''), ['nlb' => true]);
                    $key = $this->cefKey($device->device_id, $entity, $afi, $index);
                    $discovered[$key] = true;

                    $cef = $existing->get($key);
                    if ($cef === null) {
                        $cef = $device->cefSwitching()->create([
                            'device_id' => $device->device_id,
                            'entPhysicalIndex' => (int) $entity,
                            'afi' => $afi,
                            'cef_index' => (int) $index,
                            'cef_path' => $stat['cefSwitchingPath'] ?? '',
                        ]);
                        // counters default to 0 in the DB; mirror that so the *_prev math is correct
                        $cef->drop = 0;
                        $cef->punt = 0;
                        $cef->punt2host = 0;
                        $cef->updated = 0;
                        Log::info('+', ['nlb' => true]);
                        $progress->mark('+');
                    } else {
                        $progress->mark('.');
                    }

                    // Copy the 64-bit (HC) counters over the 32-bit ones where available
                    $drop = $stat['cefSwitchingDrop'] ?? 0;
                    $punt = $stat['cefSwitchingPunt'] ?? 0;
                    $punt2host = $stat['cefSwitchingPunt2Host'] ?? 0;
                    if (is_numeric($stat['cefSwitchingHCDrop'] ?? null)) {
                        $drop = $stat['cefSwitchingHCDrop'];
                    }
                    if (is_numeric($stat['cefSwitchingHCPunt'] ?? null)) {
                        $punt = $stat['cefSwitchingHCPunt'];
                    }
                    if (is_numeric($stat['cefSwitchingHCPunt2Host'] ?? null)) {
                        $punt2host = $stat['cefSwitchingHCPunt2Host'];
                    }

                    // Track the previous values so the UI can compute a rate
                    $cef->drop_prev = $cef->drop;
                    $cef->punt_prev = $cef->punt;
                    $cef->punt2host_prev = $cef->punt2host;
                    $cef->drop = $drop;
                    $cef->punt = $punt;
                    $cef->punt2host = $punt2host;
                    $cef->updated_prev = $cef->updated;
                    $cef->updated = $polled;
                    $cef->save();

                    $tags = [
                        'entity' => $entity,
                        'afi' => $afi,
                        'index' => $index,
                        'rrd_name' => ['cefswitching', $entity, $afi, $index],
                        'rrd_def' => RrdDefinition::make()
                            ->addDataset('drop', 'DERIVE', 0, 1000000)
                            ->addDataset('punt', 'DERIVE', 0, 1000000)
                            ->addDataset('hostpunt', 'DERIVE', 0, 1000000),
                    ];
                    $fields = [
                        'drop' => $drop,
                        'punt' => $punt,
                        'hostpunt' => $punt2host,
                    ];
                    $datastore->put($os->getDeviceArray(), 'cefswitching', $tags, $fields);
                }
            }
        }
        $progress->end();

        $this->deleteStale($device, array_keys($discovered));
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->cefSwitching()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->cefSwitching()->delete();
    }

    /**
     * @return array{cef_switching: Collection<int, CefSwitching>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type != 'discovery') {
            return null;
        }

        return [
            'cef_switching' => $device->cefSwitching()
                ->get()
                ->sort(fn (CefSwitching $a, CefSwitching $b) => $a->entPhysicalIndex <=> $b->entPhysicalIndex)
                ->values()
                ->makeHidden(['cef_switching_id']),
        ];
    }

    /**
     * Build a stable key identifying a cef_switching row.
     */
    private function cefKey(int $device_id, $entity, $afi, $index): string
    {
        return $device_id . '-' . (int) $entity . '-' . $afi . '-' . (int) $index;
    }

    /**
     * entPhysicalIndex => entity metadata for display in the logs.
     */
    private function entityNames(Device $device): Collection
    {
        return $device->entityPhysical()
            ->get(['entPhysicalIndex', 'entPhysicalName', 'entPhysicalModelName'])
            ->keyBy('entPhysicalIndex');
    }

    private function entityName(Collection $entities, $entity): string
    {
        $ent = $entities->get($entity);
        if ($ent === null) {
            return '';
        }

        return ($ent->entPhysicalName ?? '') . ' - ' . ($ent->entPhysicalModelName ?? '');
    }

    /**
     * Delete cef_switching rows that are no longer reported.
     *
     * @param  string[]  $discovered
     */
    private function deleteStale(Device $device, array $discovered): void
    {
        $stale = $device->cefSwitching()
            ->get()
            ->filter(fn (CefSwitching $cef) => ! in_array($this->cefKey($device->device_id, $cef->entPhysicalIndex, $cef->afi, $cef->cef_index), $discovered, true))
            ->pluck('cef_switching_id');

        if ($stale->isEmpty()) {
            return;
        }

        Log::debug('Deleting stale CISCO-CEF entries: ' . $stale->implode(', '));
        $device->cefSwitching()->whereIn('cef_switching_id', $stale)->delete();
    }
}
