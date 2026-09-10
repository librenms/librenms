<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Util\IP;
use LibreNMS\Util\Url;

class DevicesSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $devices = Device::hasAccess($user)
            ->select('devices.*')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->where(function ($query) use ($search, $like): void {
                $mac = strtolower(str_replace([':', '-', '.'], '', $search));

                $query->where(function ($q) use ($like): void {
                    $q->where('hostname', 'like', $like)
                        ->orWhere('sysName', 'like', $like)
                        ->orWhere('display', 'like', $like)
                        ->orWhere('hardware', 'like', $like)
                        ->orWhere('purpose', 'like', $like)
                        ->orWhere('serial', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('locations.location', 'like', $like);
                })
                ->when(IP::isValid($search), fn ($q) => $q->orWhere('ip', '=', inet_pton($search)));

                if (preg_match('/^[0-9.]+$/', $search) && str_contains($search, '.')) {
                    $query->orWhere(fn ($sq) => $sq->whereRelation('ports.ipv4', 'ipv4_address', 'like', $like)
                        ->orWhere('overwrite_ip', 'like', $like));
                } elseif (preg_match('/^[0-9a-f:]+$/i', $search) && str_contains($search, ':')) {
                    $query->orWhere(fn ($sq) => $sq->whereRelation('ports.ipv6', 'ipv6_address', 'like', $like)
                        ->orWhereRelation('ports.ipv6', 'ipv6_compressed', 'like', $like)
                        ->orWhereRelation('ports', 'ifPhysAddress', 'like', $like)
                        ->orWhereRelation('ports', 'ifPhysAddress', 'like', '%' . $mac . '%')
                        ->orWhere('overwrite_ip', 'like', $like));
                } elseif (ctype_xdigit($mac)) {
                    $query->orWhereRelation('ports', 'ifPhysAddress', 'like', $like)
                        ->orWhereRelation('ports', 'ifPhysAddress', 'like', '%' . $mac . '%');
                }
            })
            ->orderBy('devices.display')
            ->limit($limit)
            ->get();

        $results = $devices->map(fn (Device $device) => [
            'name' => $device->display,
            'subtitle' => implode(' · ', array_filter([
                LibrenmsConfig::getOsSetting($device->os, 'text'),
                $device->hardware,
                $device->name(),
            ])),
            'image' => $device->icon,
            'status' => match ($device->getDeviceStatus()) {
                DeviceStatus::Up, DeviceStatus::IgnoredUp => $device->isUnderMaintenance() ? 'tw:border-l-blue-500!' : 'tw:border-l-green-600!',
                DeviceStatus::Down, DeviceStatus::IgnoredDown => $device->isUnderMaintenance() ? 'tw:border-l-blue-500!' : 'tw:border-l-red-600!',
                DeviceStatus::Disabled => 'tw:border-l-black!',
                DeviceStatus::NeverPolled => 'tw:border-l-gray-400!',
            },
            'url' => Url::deviceUrl($device),
        ]);

        if ($results->isEmpty()) {
            return [null];
        }

        return [['type' => 'devices', 'label' => __('search.devices'), 'results' => $results]];
    }
}
