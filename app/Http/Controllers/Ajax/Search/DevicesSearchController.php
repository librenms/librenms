<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Util\Url;

class DevicesSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $query = Device::hasAccess($user)->select('devices.*')
                ->where('hostname', 'like', $like)
                ->orWhere('sysName', 'like', $like)
                ->orWhere('display', 'like', $like)
                ->orWhere('hardware', 'like', $like)
                ->orWhere('purpose', 'like', $like)
                ->orWhere('serial', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhereRelation('location', 'location', 'like', $like);

        $mac = strtolower(str_replace([':', '-', '.'], '', $search));

        if (preg_match('/^[0-9.]+$/', $search) && str_contains($search, '.')) {
            $query->orWhereRelation('ports.ipv4', 'ipv4_address', 'like', $like)
                ->orWhere('overwrite_ip', 'like', $like);

            if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                $query->orWhere('ip', '=', inet_pton($search));
            }
        } elseif (preg_match('/^[0-9a-f:]+$/i', $search) && str_contains($search, ':')) {
            $query->orWhereRelation('ports.ipv6', 'ipv6_address', 'like', $like)
                ->orWhereRelation('ports.ipv6', 'ipv6_compressed', 'like', $like)
                ->orWhereRelation('ports', 'ifPhysAddress', 'like', '%' . $mac . '%')
                ->orWhere('overwrite_ip', 'like', $like);

            if (\LibreNMS\Util\IPv6::isValid($search, false)) {
                $query->orWhere('ip', '=', inet_pton($search));
            }
        } elseif (ctype_xdigit($mac)) {
            $query->orWhereRelation('ports', 'ifPhysAddress', 'like', '%' . $mac . '%');
        }

        // A MAC-style search (with or without separators) can also match FDB entries
        if (LibrenmsConfig::get('webui.global_search_device_fdb') && ctype_xdigit($mac)) {
            $query->orWhereRelation('portsFdb', 'mac_address', 'like', '%' . $mac . '%');
        }

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
