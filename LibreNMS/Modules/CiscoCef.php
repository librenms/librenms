<?php

namespace LibreNMS\Modules;

use App\Models\CefSwitching;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use SnmpQuery;

class CiscoCef implements Module
{
    use RequiresPollingMethod;
    use SyncsModels;

    /**
     * @return string[]
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

        $models = collect();
        foreach ($cefs as $entity => $afis) {
            $entity_name = $this->entityName($entities, $entity);
            Log::info("\n$entity $entity_name");
            foreach ($afis as $afi => $paths) {
                Log::info(" |- $afi");
                foreach ($paths as $path => $path_name) {
                    Log::info(' | |-' . $path . ': ' . $path_name['cefSwitchingPath']);
                    $models->push(new CefSwitching([
                        'device_id' => $device->device_id,
                        'entPhysicalIndex' => (int) $entity,
                        'afi' => $afi,
                        'cef_index' => (int) $path,
                        'cef_path' => $path_name['cefSwitchingPath'],
                    ]));
                }
            }
        }

        ModuleModelObserver::observe(CefSwitching::class, 'CISCO CEF');
        $this->syncModels($device, 'cefSwitching', $models);
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
            ->keyBy(fn (CefSwitching $cef) => $cef->getCompositeKey());

        $models = collect();
        foreach ($cefs as $entity => $afis) {
            $entity_name = $this->entityName($entities, $entity);
            Log::info("\n$entity $entity_name");
            foreach ($afis as $afi => $paths) {
                Log::info(" |- $afi");
                foreach ($paths as $index => $stat) {
                    Log::info(' | |-' . $index . ': ' . ($stat['cefSwitchingPath'] ?? ''), ['nlb' => true]);

                    $model = new CefSwitching([
                        'device_id' => $device->device_id,
                        'entPhysicalIndex' => (int) $entity,
                        'afi' => $afi,
                        'cef_index' => (int) $index,
                        'cef_path' => $stat['cefSwitchingPath'] ?? '',
                    ]);
                    $old = $existing->get($model->getCompositeKey());

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
                    $model->drop = $drop;
                    $model->punt = $punt;
                    $model->punt2host = $punt2host;
                    $model->drop_prev = $old === null ? 0 : (int) $old->drop;
                    $model->punt_prev = $old === null ? 0 : (int) $old->punt;
                    $model->punt2host_prev = $old === null ? 0 : (int) $old->punt2host;
                    $model->updated = $polled;
                    $model->updated_prev = $old === null ? 0 : (int) $old->updated;

                    $models->push($model);

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

        ModuleModelObserver::observe(CefSwitching::class, 'CISCO CEF');
        $this->syncModels($device, 'cefSwitching', $models);
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
     * entPhysicalIndex => entity metadata for display in the logs.
     *
     * @return Collection<int, EntPhysical>
     */
    private function entityNames(Device $device): Collection
    {
        return $device->entityPhysical()
            ->get(['entPhysicalIndex', 'entPhysicalName', 'entPhysicalModelName'])
            ->keyBy('entPhysicalIndex');
    }

    /**
     * @param  Collection<int, EntPhysical>  $entities
     * @param  int  $entity
     */
    private function entityName(Collection $entities, $entity): string
    {
        $ent = $entities->get($entity);
        if ($ent === null) {
            return '';
        }

        return ($ent->entPhysicalName ?? '') . ' - ' . ($ent->entPhysicalModelName ?? '');
    }
}
