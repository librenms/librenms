<?php

namespace App\Http\Controllers\Ajax;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\Sensor;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $eventlog = Eventlog::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('message', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('username', 'like', $like))
            ->orderBy('event_id', 'desc')->limit($limit)->get()
            ->map(fn (Eventlog $e) => [
                'name' => $e->message,
                'subtitle' => trim($e->device?->display . ' ' . $e->datetime),
                'icon' => 'fa fa-bookmark',
                'url' => Url::deviceUrl($e->device_id, ['tab' => 'logs']),
            ]);
        if ($eventlog->isNotEmpty()) {
            $groups[] = ['type' => 'eventlog', 'label' => __('Eventlog'), 'results' => $eventlog];
        }

        return response()->json(['groups' => $groups]);
    }
}
