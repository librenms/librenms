<?php

namespace App\Http\Controllers\Ajax;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Mempool;
use App\Models\Port;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\Storage;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Url;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = trim((string) $request->input('search'));
        if ($search === '') {
            return response()->json(['groups' => []]);
        }

        $like = '%' . $search . '%';
        $limit = (int) LibrenmsConfig::get('webui.global_search_result_limit') ?: 8;
        $groups = [];

        $deviceQuery = Device::hasAccess($user)->select('devices.*')->distinct();
        $deviceQuery->where(function (Builder $q) use ($like, $search, $deviceQuery) {
            $q->where('hostname', 'like', $like)
                ->orWhere('sysName', 'like', $like)
                ->orWhere('display', 'like', $like)
                ->orWhere('hardware', 'like', $like)
                ->orWhere('purpose', 'like', $like)
                ->orWhere('serial', 'like', $like)
                ->orWhere('notes', 'like', $like);

            if (preg_match('/^[0-9.]+$/', $search) && str_contains($search, '.')) {
                $deviceQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                    ->leftJoin('ipv4_addresses', 'ipv4_addresses.port_id', '=', 'ports.port_id');
                $q->orWhere('ipv4_addresses.ipv4_address', 'like', $like)
                    ->orWhere('overwrite_ip', 'like', $like);
                if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (preg_match('/^[0-9a-f:]+$/i', $search) && str_contains($search, ':')) {
                $deviceQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                    ->leftJoin('ipv6_addresses', 'ipv6_addresses.port_id', '=', 'ports.port_id');
                $q->orWhere('ipv6_addresses.ipv6_address', 'like', $like)
                    ->orWhere('overwrite_ip', 'like', $like)
                    ->orWhere('ports.ifPhysAddress', 'like', '%' . str_replace(':', '', $search) . '%');
                if (\LibreNMS\Util\IPv6::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (ctype_xdigit($mac = str_replace([':', '-'], '', $search))) {
                $deviceQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id');
                $q->orWhere('ports.ifPhysAddress', 'like', '%' . $mac . '%');
            }
        });

        $devices = $deviceQuery->orderBy('hostname')->limit($limit)->get()
            ->map(fn (Device $d) => [
                'name' => $d->display,
                'subtitle' => trim(LibrenmsConfig::getOsSetting($d->os, 'text') . ' ' . $d->hardware) ?: $d->sysName,
                'image' => $d->icon,
                'status' => match ($d->getDeviceStatus()) {
                    DeviceStatus::Up, DeviceStatus::IgnoredUp => $d->isUnderMaintenance() ? 'tw:border-l-blue-500!' : 'tw:border-l-green-600!',
                    DeviceStatus::Down, DeviceStatus::IgnoredDown => $d->isUnderMaintenance() ? 'tw:border-l-blue-500!' : 'tw:border-l-red-600!',
                    DeviceStatus::Disabled => 'tw:border-l-black!',
                    DeviceStatus::NeverPolled => 'tw:border-l-gray-400!',
                },
                'url' => Url::deviceUrl($d),
            ]);
        if ($devices->isNotEmpty()) {
            $groups[] = ['type' => 'devices', 'label' => __('Devices'), 'results' => $devices];
        }

        $ports = Port::hasAccess($user)->with('device')->where('deleted', 0)
            ->where(fn (Builder $q) => $q->where('ifAlias', 'like', $like)
                ->orWhere('ifDescr', 'like', $like)
                ->orWhere('ifName', 'like', $like)
                ->orWhere('portName', 'like', $like)
                ->orWhere('port_descr_descr', 'like', $like))
            ->orderBy('ifDescr')->limit($limit)->get()
            ->map(fn (Port $p) => [
                'name' => $p->getLabel(),
                'subtitle' => trim($p->device?->display . ' ' . $p->getDescription()),
                'icon' => 'fa fa-link',
                'status' => match (true) {
                    (bool) $p->ignore => 'tw:border-l-black!',
                    $p->ifAdminStatus == IfOperStatus::Down => 'tw:border-l-gray-400!',
                    $p->ifOperStatus != IfOperStatus::Up => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::portUrl($p),
            ]);
        if ($ports->isNotEmpty()) {
            $groups[] = ['type' => 'ports', 'label' => __('Ports'), 'results' => $ports];
        }

        $bgp = BgpPeer::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('astext', 'like', $like)
                ->orWhere('bgpPeerDescr', 'like', $like)
                ->orWhere('bgpPeerIdentifier', 'like', $like)
                ->orWhere('bgpPeerRemoteAs', 'like', $like))
            ->orderBy('astext')->limit($limit)->get()
            ->map(fn (BgpPeer $b) => [
                'name' => $b->bgpPeerIdentifier,
                'subtitle' => trim($b->device?->display . ' AS' . $b->bgpPeerRemoteAs . ' ' . $b->astext),
                'icon' => 'fa fa-share-alt',
                'status' => match (true) {
                    $b->bgpPeerAdminStatus !== 'start' => 'tw:border-l-black!',
                    $b->bgpPeerState !== 'established' => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::deviceUrl($b->device, ['tab' => 'routing', 'proto' => 'bgp']),
            ]);
        if ($bgp->isNotEmpty()) {
            $groups[] = ['type' => 'bgp', 'label' => __('BGP Sessions'), 'results' => $bgp];
        }

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
                    'page' => 'graphs',
                    'id' => $s->sensor_id,
                    'type' => 'sensor_' . $s->sensor_class,
                    'from' => LibrenmsConfig::get('time.day'),
                    'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);
        if ($sensors->isNotEmpty()) {
            $groups[] = ['type' => 'sensors', 'label' => __('Health'), 'results' => $sensors];
        }

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
                    'page' => 'graphs',
                    'id' => $s->sensor_id,
                    'type' => 'wireless_' . $s->sensor_class->value,
                    'from' => LibrenmsConfig::get('time.day'),
                    'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);
        if ($wireless->isNotEmpty()) {
            $groups[] = ['type' => 'wireless', 'label' => __('Wireless'), 'results' => $wireless];
        }

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
                    'page' => 'graphs',
                    'id' => $s->storage_id,
                    'type' => 'storage_usage',
                    'from' => LibrenmsConfig::get('time.day'),
                    'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);
        if ($storage->isNotEmpty()) {
            $groups[] = ['type' => 'storage', 'label' => __('Storage'), 'results' => $storage];
        }

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
                    'page' => 'graphs',
                    'id' => $m->mempool_id,
                    'type' => 'mempool_usage',
                    'from' => LibrenmsConfig::get('time.day'),
                    'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);
        if ($mempools->isNotEmpty()) {
            $groups[] = ['type' => 'mempools', 'label' => __('Memory'), 'results' => $mempools];
        }

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
                    'page' => 'graphs',
                    'id' => $p->processor_id,
                    'type' => 'processor_usage',
                    'from' => LibrenmsConfig::get('time.day'),
                    'to' => LibrenmsConfig::get('time.now'),
                ]),
            ]);
        if ($processors->isNotEmpty()) {
            $groups[] = ['type' => 'processors', 'label' => __('Processors'), 'results' => $processors];
        }

        $eventlog = Eventlog::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('message', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('username', 'like', $like))
            ->orderBy('event_id', 'desc')->limit($limit)->get()
            ->map(fn (Eventlog $e) => [
                'name' => $e->message,
                'subtitle' => trim($e->device?->display . ' ' . $e->datetime),
                'icon' => 'fa fa-bookmark',
                'status' => match ($e->severity) {
                    Severity::Ok => 'tw:border-l-green-600!',
                    Severity::Info, Severity::Notice => 'tw:border-l-blue-500!',
                    Severity::Warning => 'tw:border-l-amber-500!',
                    Severity::Error => 'tw:border-l-red-600!',
                    default => 'tw:border-l-gray-400!',
                },
                'url' => Url::deviceUrl($e->device_id, ['tab' => 'logs']),
            ]);
        if ($eventlog->isNotEmpty()) {
            $groups[] = ['type' => 'eventlog', 'label' => __('Eventlog'), 'results' => $eventlog];
        }

        return response()->json(['groups' => $groups]);
    }
}
