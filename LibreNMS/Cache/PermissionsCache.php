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

use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;

class PermissionsCache
{
    private $devicePermissions;
    private $portPermissions;
    private $billPermissions;
    private $deviceGroupMap;

    /**
     * Check if a device can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     *
     * @param \App\Models\Device|int $device
     * @param \App\Models\User|int $user
     * @return bool
     */
    public function canAccessDevice($device, $user = null)
    {
        return $this->getDevicePermissions($user)
            ->contains('device_id', $this->getDeviceId($device));
    }

    /**
     * Check if a access can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     *
     * @param \App\Models\Port|int $port
     * @param \App\Models\User|int $user
     * @return bool
     */
    public function canAccessPort($port, $user = null)
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('port_id', $this->getPortId($port))
            ->isNotEmpty();
    }

    /**
     * Check if a bill can be accessed by user (non-global read/admin)
     * If no user is given, use the logged in user
     *
     * @param \App\Models\Bill|int $bill
     * @param \App\Models\User|int $user
     * @return bool
     */
    public function canAccessBill($bill, $user = null)
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('bill_id', $this->getBillId($bill))
            ->isNotEmpty();
    }

    /**
     * Get the user_id of users that have been granted access to device
     *
     * @param \App\Models\Device|int $device
     * @return \Illuminate\Support\Collection
     */
    /*
        public function usersForDevice($device)
        {
            return $this->getDevicePermissions()
                ->where('device_id', $this->getDeviceId($device))
                ->pluck('user_id');
        }
    */

    /**
     * Get the user_id of users that have been granted access to port
     *
     * @param \App\Models\Port|int $port
     * @return \Illuminate\Support\Collection
     */
    public function usersForPort($port)
    {
        return $this->getPortPermissions()
            ->where('port_id', $this->getPortId($port))
            ->pluck('user_id');
    }

    /**
     * Get the user_id of users that have been granted access to bill
     *
     * @param \App\Models\Bill|int $bill
     * @return \Illuminate\Support\Collection
     */
    public function usersForBill($bill)
    {
        return $this->getBillPermissions()
            ->where('bill_id', $this->getBillId($bill))
            ->pluck('user_id');
    }

    /**
     * Get a list of device_id of all devices the user can access
     *
     * @param \App\Models\User|int $user
     * @return \Illuminate\Support\Collection
     */
    public function devicesForUser($user = null)
    {
        return $this->getDevicePermissions($user)
            ->pluck('device_id');
    }

    /**
     * Get a list of port_id of all ports the user can access directly
     *
     * @param \App\Models\User|int $user
     * @return \Illuminate\Support\Collection
     */
    public function portsForUser($user = null)
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('port_id');
    }

    /**
     * Get a list of bill_id of all bills the user can access directly
     *
     * @param \App\Models\User|int $user
     * @return \Illuminate\Support\Collection
     */
    public function billsForUser($user = null)
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('bill_id');
    }

    /**
     * Get the ids of all device groups the user can access
     *
     * @param \App\Models\User|int $user
     * @return \Illuminate\Support\Collection
     */
    public function deviceGroupsForUser($user = null)
    {
        $user_id = $this->getUserId($user);

        // if we don't have a map for this user yet, populate it.
        if (! isset($this->deviceGroupMap[$user_id])) {
            $this->deviceGroupMap[$user_id] = DB::table('device_group_device')
                ->whereIn('device_id', $this->devicesForUser($user))
                ->distinct('device_group_id')
                ->pluck('device_group_id');
        }

        return $this->deviceGroupMap[$user_id];
    }

    /**
     * Get the cached data for device permissions.  Use helpers instead.
     *
     * @param \App\Models\User|int $user
     * @return \Illuminate\Support\Collection
     */
    public function getDevicePermissions($user = null)
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
     * @return \Illuminate\Support\Collection
     */
    public function getPortPermissions()
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
     * @return \Illuminate\Support\Collection
     */
    public function getBillPermissions()
    {
        if (is_null($this->billPermissions)) {
            $this->billPermissions = DB::table('bill_perms')
                ->select(['user_id', 'bill_id'])->get();
        }

        return $this->billPermissions;
    }

    /**
     * @param mixed $user
     * @return int|null
     */
    private function getUserId($user)
    {
        return $user instanceof User ? $user->user_id : (is_numeric($user) ? (int) $user : Auth::id());
    }

    /**
     * @param mixed $device
     * @return int
     */
    private function getDeviceId($device)
    {
        return $device instanceof Device ? $device->device_id : (is_numeric($device) ? (int) $device : 0);
    }

    /**
     * @param mixed $port
     * @return int
     */
    private function getPortId($port)
    {
        return $port instanceof Port ? $port->port_id : (is_numeric($port) ? (int) $port : 0);
    }

    /**
     * @param mixed $bill
     * @return int
     */
    private function getBillId($bill)
    {
        return $bill instanceof Bill ? $bill->bill_id : (is_numeric($bill) ? (int) $bill : 0);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getDeviceGroupPermissionsQuery()
    {
        return DB::table('devices_group_perms')
        ->select('devices_group_perms.user_id', 'device_group_device.device_id')
        ->join('device_group_device', 'device_group_device.device_group_id', '=', 'devices_group_perms.device_group_id')
        ->when(! Config::get('permission.device_group.allow_dynamic'), function ($query) {
            return $query
                ->join('device_groups', 'device_groups.id', '=', 'devices_group_perms.device_group_id')
                ->where('device_groups.type', 'static');
        });
    }
}
