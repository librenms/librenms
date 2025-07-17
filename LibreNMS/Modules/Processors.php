<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Processor;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;
use SnmpQuery;

class Processors implements Module
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
        $processors = $os->discoverProcessors();
        ModuleModelObserver::observe(Processor::class);
        $this->syncModels($os->getDevice(), 'processors', $processors);
        ModuleModelObserver::done();
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $processors = $os->getDevice()->processors;

        if ($processors->isEmpty()) {
            return;
        }

        $processors->each(fn (Processor $p) => $p->processor_usage = null);

        if ($os instanceof ProcessorPolling) {
            $processors = $os->pollProcessors($processors);
        } else {
            $oids = $processors->pluck('processor_oid')->filter()->all();
            if ($oids) {
                $data = SnmpQuery::numeric()->get($oids)->values();
                foreach ($processors as $processor) {
                    if (array_key_exists($processor->processor_oid, $data)) {
                        $processor->processor_usage = Number::cast($data[$processor->processor_oid]);
                    }
                }
            }
        }

        // update
        $rrd_def = RrdDefinition::make()->addDataset('usage', 'GAUGE', -273, 1000);
        foreach ($processors as $processor) {
            $usage = $processor->processor_usage === null ? null : round($processor->processor_usage, 2);
            Log::info("$processor->processor_descr: $usage%");

            $rrd_name = ['processor', $processor->processor_type, $processor->processor_index];
            $tags = ['processor_type' =>  $processor->processor_type, 'processor_index' => $processor->processor_index, 'rrd_name' => $rrd_name, 'rrd_def' => $rrd_def];
            $fields = ['usage' => $usage];
            $datastore->put($os->getDeviceArray(), 'processors', $tags, $fields);

            if ($usage !== null) {
                $processor->save();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->processors()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->processors()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'processors' => $device->processors()
                ->orderBy('processor_type')
                ->orderBy('processor_index')
                ->get()->makeHidden(['device_id', 'processor_id']),
        ];
    }
}
