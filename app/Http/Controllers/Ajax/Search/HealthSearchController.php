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
        $sensors = Sensor::hasAccess($user)->with('device')->where('sensor_deleted', 0)
            ->where(fn (Builder $q) => $q->where('sensor_descr', 'like', $like)
                ->orWhere('sensor_class', 'like', $like)
                ->orWhere('sensor_type', 'like', $like))
            ->orderBy('sensor_descr')->limit($limit)->get()
            ->map(fn (Sensor $s) => [
                'name' => $s->sensor_descr,
                'subtitle' => trim($s->device?->display . ' ' . $s->sensor_class),
                'icon' => 'fa fa-heartbeat',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $s->sensor_id, 'type' => 'sensor_' . $s->sensor_class,
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $wireless = WirelessSensor::hasAccess($user)->with('device')->where('sensor_deleted', 0)
            ->where(fn (Builder $q) => $q->where('sensor_descr', 'like', $like)
                ->orWhere('sensor_class', 'like', $like)
                ->orWhere('sensor_type', 'like', $like))
            ->orderBy('sensor_descr')->limit($limit)->get()
            ->map(fn (WirelessSensor $s) => [
                'name' => $s->sensor_descr,
                'subtitle' => trim($s->device?->display . ' ' . $s->sensor_class->value),
                'icon' => 'fa fa-wifi',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $s->sensor_id, 'type' => 'wireless_' . $s->sensor_class->value,
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $storage = Storage::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('storage_descr', 'like', $like)
                ->orWhere('storage_type', 'like', $like))
            ->orderBy('storage_descr')->limit($limit)->get()
            ->map(fn (Storage $s) => [
                'name' => $s->storage_descr,
                'subtitle' => trim($s->device?->display . ' ' . $s->storage_type),
                'icon' => 'fa fa-hdd-o',
                'status' => ($s->storage_perc_warn !== null && $s->storage_perc >= $s->storage_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $s->storage_id, 'type' => 'storage_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $mempools = Mempool::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('mempool_descr', 'like', $like)
                ->orWhere('mempool_type', 'like', $like))
            ->orderBy('mempool_descr')->limit($limit)->get()
            ->map(fn (Mempool $m) => [
                'name' => $m->mempool_descr,
                'subtitle' => trim($m->device?->display . ' ' . $m->mempool_type),
                'icon' => 'fa fa-memory',
                'status' => ($m->mempool_perc_warn !== null && $m->mempool_perc >= $m->mempool_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $m->mempool_id, 'type' => 'mempool_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        $processors = Processor::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('processor_descr', 'like', $like)
                ->orWhere('processor_type', 'like', $like))
            ->orderBy('processor_descr')->limit($limit)->get()
            ->map(fn (Processor $p) => [
                'name' => $p->processor_descr,
                'subtitle' => trim($p->device?->display . ' ' . $p->processor_type),
                'icon' => 'fa fa-microchip',
                'status' => ($p->processor_perc_warn !== null && $p->processor_usage >= $p->processor_perc_warn) ? 'tw:border-l-red-600!' : 'tw:border-l-green-600!',
                'url' => Url::generate([
                    'page' => 'graphs', 'id' => $p->processor_id, 'type' => 'processor_usage',
                    'from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);

        return [
            $sensors->isEmpty() ? null : ['type' => 'sensors', 'label' => __('Health'), 'results' => $sensors],
            $wireless->isEmpty() ? null : ['type' => 'wireless', 'label' => __('Wireless'), 'results' => $wireless],
            $storage->isEmpty() ? null : ['type' => 'storage', 'label' => __('Storage'), 'results' => $storage],
            $mempools->isEmpty() ? null : ['type' => 'mempools', 'label' => __('Memory'), 'results' => $mempools],
            $processors->isEmpty() ? null : ['type' => 'processors', 'label' => __('Processors'), 'results' => $processors],
        ];
    }
}
