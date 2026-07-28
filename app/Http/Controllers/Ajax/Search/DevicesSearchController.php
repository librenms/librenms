<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Location;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Util\Url;

class DevicesSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $query = Device::hasAccess($user)->select('devices.*');
        $query->where(function (Builder $q) use ($like, $search): void {
            $q->where('hostname', 'like', $like)
                ->orWhere('sysName', 'like', $like)
                ->orWhere('display', 'like', $like)
                ->orWhere('hardware', 'like', $like)
                ->orWhere('purpose', 'like', $like)
                ->orWhere('serial', 'like', $like)
                ->orWhere('notes', 'like', $like);

            $locationquery = Location::where('location', 'like', $like);
            $q->orWhereIn('devices.location_id', $locationquery->pluck('id'));

            $mac = strtolower(str_replace([':', '-'], '', $search));

            if (preg_match('/^[0-9.]+$/', $search) && str_contains($search, '.')) {
                $portquery = Port::query()
                    ->leftJoin('ipv4_addresses', 'ipv4_addresses.port_id', '=', 'ports.port_id')
                    ->where('ipv4_addresses.ipv4_address', 'like', $like);

                $q->orWhereIn('devices.device_id', $portquery->pluck('ports.device_id'))
                    ->orWhere('overwrite_ip', 'like', $like);

                if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (preg_match('/^[0-9a-f:]+$/i', $search) && str_contains($search, ':')) {
                $portquery = Port::query()
                    ->leftJoin('ipv6_addresses', 'ipv6_addresses.port_id', '=', 'ports.port_id')
                    ->where('ipv6_addresses.ipv6_address', 'like', $like)
                    ->orWhere('ipv6_addresses.ipv6_compressed', 'like', $like)
                    ->orWhere('ports.ifPhysAddress', 'like', '%' . $mac . '%');

                $q->orWhereIn('devices.device_id', $portquery->pluck('ports.device_id'))
                    ->orWhere('overwrite_ip', 'like', $like);

                if (\LibreNMS\Util\IPv6::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (ctype_xdigit($mac)) {
                $portquery = Port::where('ifPhysAddress', 'like', '%' . $mac . '%');
                $q->orWhereIn('devices.device_id', $portquery->pluck('ports.device_id'));
            }

            // A MAC-style search (with or without separators) can also match FDB entries
            if (ctype_xdigit($mac)) {
                $fdbquery = PortsFdb::where('mac_address', 'like', '%' . $mac . '%');
                $q->orWhereIn('devices.device_id', $fdbquery->pluck('device_id'));
            }
        });

        $devices = $query->orderBy('display')->limit($limit)->get()
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

        return [$devices->isEmpty() ? null : ['type' => 'devices', 'label' => __('Devices'), 'results' => $devices]];
    }
}
