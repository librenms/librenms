<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Mempool;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\Storage;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Url;

class HealthSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.health')) {
            return [null];
        }

        $sensors = Sensor::hasAccess($user)->with('device')->where('sensor_deleted', 0)
            ->where(fn (Builder $q) => $q->where('sensor_descr', 'like', $like)
                ->orWhere('sensor_class', 'like', $like)
                ->orWhere('sensor_type', 'like', $like))
            ->orderBy('sensor_descr')->limit($limit)->get()
            ->map(fn (Sensor $sensor) => [
                'name' => $sensor->sensor_descr,
                'subtitle' => implode(' · ', array_filter([$sensor->device?->display, $sensor->sensor_class])),
                'icon' => 'fa fa-heartbeat',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $sensor->sensor_id, 'type' => 'sensor_' . $sensor->sensor_class,
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $wireless = WirelessSensor::hasAccess($user)->with('device')->where('sensor_deleted', 0)
            ->where(fn (Builder $q) => $q->where('sensor_descr', 'like', $like)
                ->orWhere('sensor_class', 'like', $like)
                ->orWhere('sensor_type', 'like', $like))
            ->orderBy('sensor_descr')->limit($limit)->get()
            ->map(fn (WirelessSensor $sensor) => [
                'name' => $sensor->sensor_descr,
                'subtitle' => implode(' · ', array_filter([$sensor->device?->display, $sensor->sensor_class->value])),
                'icon' => 'fa fa-wifi',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $sensor->sensor_id, 'type' => 'wireless_' . $sensor->sensor_class->value,
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $storage = Storage::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('storage_descr', 'like', $like)
                ->orWhere('storage_type', 'like', $like))
            ->orderBy('storage_descr')->limit($limit)->get()
            ->map(fn (Storage $storage) => [
                'name' => $storage->storage_descr,
                'subtitle' => implode(' · ', array_filter([$storage->device?->display, $storage->storage_type])),
                'icon' => 'fa fa-hdd-o',
                'status' => ($storage->storage_perc_warn !== null && $storage->storage_perc >= $storage->storage_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $storage->storage_id, 'type' => 'storage_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $mempools = Mempool::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('mempool_descr', 'like', $like)
                ->orWhere('mempool_type', 'like', $like))
            ->orderBy('mempool_descr')->limit($limit)->get()
            ->map(fn (Mempool $mempool) => [
                'name' => $mempool->mempool_descr,
                'subtitle' => implode(' · ', array_filter([$mempool->device?->display, $mempool->mempool_type])),
                'icon' => 'fa fa-memory',
                'status' => ($mempool->mempool_perc_warn !== null && $mempool->mempool_perc >= $mempool->mempool_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $mempool->mempool_id, 'type' => 'mempool_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $processors = Processor::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('processor_descr', 'like', $like)
                ->orWhere('processor_type', 'like', $like))
            ->orderBy('processor_descr')->limit($limit)->get()
            ->map(fn (Processor $processor) => [
                'name' => $processor->processor_descr,
                'subtitle' => implode(' · ', array_filter([$processor->device?->display, $processor->processor_type])),
                'icon' => 'fa fa-microchip',
                'status' => ($processor->processor_perc_warn !== null && $processor->processor_usage >= $processor->processor_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $processor->processor_id, 'type' => 'processor_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        return [
            $sensors->isEmpty() ? null : ['type' => 'sensors', 'label' => __('search.health'), 'results' => $sensors],
            $wireless->isEmpty() ? null : ['type' => 'wireless', 'label' => __('search.wireless'), 'results' => $wireless],
            $storage->isEmpty() ? null : ['type' => 'storage', 'label' => __('search.storage'), 'results' => $storage],
            $mempools->isEmpty() ? null : ['type' => 'mempools', 'label' => __('search.memory'), 'results' => $mempools],
            $processors->isEmpty() ? null : ['type' => 'processors', 'label' => __('search.processors'), 'results' => $processors],
        ];
    }
}
