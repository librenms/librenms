<?php

/*
 * PermissionsCache.php
 *
 * Class to check the direct permissions on devices, ports, and bills for normal users (not global read only and admin)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2019-2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Cache;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionsCache
{
    /** @var array<int, Collection<object{user_id: int, device_id: int}>> */
    private array $devicePermissions = [];

    /** @var array<int, Collection<int>> */
    private array $deviceGroupMap = [];

    /** @var Collection<object{user_id: int, port_id: int}>|null */
    private ?Collection $portPermissions = null;

    /** @var Collection<object{user_id: int, bill_id: int}>|null */
    private ?Collection $billPermissions = null;

    /**
     * Check if a device can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     */
    public function canAccessDevice(Device|int $device, User|int|null $user = null): bool
    {
        return $this->getDevicePermissions($user)
            ->contains('device_id', $this->getDeviceId($device));
    }

    /**
     * Check if a access can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     */
    public function canAccessPort(Port|int $port, User|int|null $user = null): bool
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('port_id', $this->getPortId($port))
            ->isNotEmpty();
    }

    /**
     * Check if a bill can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     */
    public function canAccessBill(Bill|int $bill, User|int|null $user = null): bool
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('bill_id', $this->getBillId($bill))
            ->isNotEmpty();
    }

    /**
     * Get the user_id of users that have been granted access to port
     *
     * @return Collection<int>
     */
    public function usersForPort(Port|int $port): Collection
    {
        return $this->getPortPermissions()
            ->where('port_id', $this->getPortId($port))
            ->pluck('user_id');
    }

    /**
     * Get the user_id of users that have been granted access to bill
     *
     * @return Collection<int>
     */
    public function usersForBill(Bill|int $bill): Collection
    {
        return $this->getBillPermissions()
            ->where('bill_id', $this->getBillId($bill))
            ->pluck('user_id');
    }

    /**
     * Get a list of device_id of all devices the user can access
     *
     * @return Collection<int>
     */
    public function devicesForUser(User|int|null $user = null): Collection
    {
        return $this->getDevicePermissions($user)
            ->pluck('device_id');
    }

    /**
     * Get a list of port_id of all ports the user can access directly
     *
     * @return Collection<int>
     */
    public function portsForUser(User|int|null $user = null): Collection
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('port_id');
    }

    /**
     * Get a list of bill_id of all bills the user can access directly
     *
     * @return Collection<int>
     */
    public function billsForUser(User|int|null $user = null): Collection
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('bill_id');
    }

    /**
     * Get the ids of all device groups the user can access
     *
     * @return Collection<int>
     */
    public function deviceGroupsForUser(User|int|null $user = null): Collection
    {
        $user_id = $this->getUserId($user);

        // if we don't have a map for this user yet, populate it.
        if (! isset($this->deviceGroupMap[$user_id])) {
            $this->deviceGroupMap[$user_id] = DB::table('device_group_device')
                ->whereIntegerInRaw('device_id', $this->devicesForUser($user))
                ->distinct('device_group_id')
                ->pluck('device_group_id');
        }

        return $this->deviceGroupMap[$user_id];
    }

    /**
     * Get the cached data for device permissions.  Use helpers instead.
     *
     * @return Collection<object{user_id: int, device_id: int}>
     */
    public function getDevicePermissions(User|int|null $user = null): Collection
    {
        $user_id = $this->getUserId($user);

        if (! isset($this->devicePermissions[$user_id])) {
            $this->devicePermissions[$user_id] = DB::table('devices_perms')
                ->select(['user_id', 'device_id'])
                ->where('user_id', $user_id)
                ->union($this->getDeviceGroupPermissionsQuery()->where('user_id', $user_id))
                ->get();
        }

        return $this->devicePermissions[$user_id];
    }

    /**
     * Get the cached data for port permissions.  Use helpers instead.
     *
     * @return Collection<object{user_id: int, port_id: int}>
     */
    public function getPortPermissions(): Collection
    {
        if (is_null($this->portPermissions)) {
            $this->portPermissions = DB::table('ports_perms')
                ->select(['user_id', 'port_id'])
                ->get();
        }

        return $this->portPermissions;
    }

    /**
     * Get the cached data for bill permissions.  Use helpers instead.
     *
     * @return Collection<object{user_id: int, bill_id: int}>
     */
    public function getBillPermissions(): Collection
    {
        if (is_null($this->billPermissions)) {
            $this->billPermissions = DB::table('bill_perms')
                ->select(['user_id', 'bill_id'])->get();
        }

        return $this->billPermissions;
    }

    public function invalidateCache(): void
    {
        $this->devicePermissions = [];
        $this->deviceGroupMap = [];
        $this->portPermissions = null;
        $this->billPermissions = null;
    }

    private function getUserId(User|int|null $user): ?int
    {
        return $user instanceof User ? $user->user_id : (is_numeric($user) ? (int) $user : Auth::id());
    }

    private function getDeviceId(Device|int|null $device): int
    {
        return $device instanceof Device ? $device->device_id : (is_numeric($device) ? (int) $device : 0);
    }

    private function getPortId(Port|int|null $port): int
    {
        return $port instanceof Port ? $port->port_id : (is_numeric($port) ? (int) $port : 0);
    }

    private function getBillId(Bill|int|null $bill): int
    {
        return $bill instanceof Bill ? $bill->bill_id : (is_numeric($bill) ? (int) $bill : 0);
    }

    private function getDeviceGroupPermissionsQuery(): Builder
    {
        return DB::table('devices_group_perms')
            ->select('devices_group_perms.user_id', 'device_group_device.device_id')
            ->join('device_group_device', 'device_group_device.device_group_id', '=',
                'devices_group_perms.device_group_id')
            ->when(! LibrenmsConfig::get('permission.device_group.allow_dynamic'), fn ($query) => $query
                ->join('device_groups', 'device_groups.id', '=', 'devices_group_perms.device_group_id')
                ->where('device_groups.type', 'static'));
    }
}
