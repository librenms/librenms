<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Util\Url;

class DevicesSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $query = Device::hasAccess($user)->select('devices.*')->distinct();
        $query->where(function (Builder $q) use ($like, $search, $query): void {
            $q->where('hostname', 'like', $like)
                ->orWhere('sysName', 'like', $like)
                ->orWhere('display', 'like', $like)
                ->orWhere('hardware', 'like', $like)
                ->orWhere('purpose', 'like', $like)
                ->orWhere('serial', 'like', $like)
                ->orWhere('notes', 'like', $like);

            if (preg_match('/^[0-9.]+$/', $search) && str_contains($search, '.')) {
                $query->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                    ->leftJoin('ipv4_addresses', 'ipv4_addresses.port_id', '=', 'ports.port_id');
                $q->orWhere('ipv4_addresses.ipv4_address', 'like', $like)
                    ->orWhere('overwrite_ip', 'like', $like);
                if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (preg_match('/^[0-9a-f:]+$/i', $search) && str_contains($search, ':')) {
                $query->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                    ->leftJoin('ipv6_addresses', 'ipv6_addresses.port_id', '=', 'ports.port_id');
                $q->orWhere('ipv6_addresses.ipv6_address', 'like', $like)
                    ->orWhere('overwrite_ip', 'like', $like)
                    ->orWhere('ports.ifPhysAddress', 'like', '%' . str_replace(':', '', $search) . '%');
                if (\LibreNMS\Util\IPv6::isValid($search, false)) {
                    $q->orWhere('ip', '=', inet_pton($search));
                }
            } elseif (ctype_xdigit($mac = str_replace([':', '-'], '', $search))) {
                $query->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id');
                $q->orWhere('ports.ifPhysAddress', 'like', '%' . $mac . '%');
            }
        });

        $devices = $query->orderBy('hostname')->limit($limit)->get()
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

        return [$this->group('devices', __('Devices'), $devices)];
    }
}
