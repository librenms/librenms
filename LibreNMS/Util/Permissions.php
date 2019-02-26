<?php
/**
 * Permissions.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Collection;

class Permissions
{
    private $permissions;
    private $permissionsByUser;
    private $permissionsById; // permissions cache

    public function getPermissions(): Collection
    {
        if (is_null($this->permissions)) {
            $this->permissions = new Collection([
                'devices' => \DB::table('devices_perms')->get(),
                'ports' => \DB::table('ports_perms')->get(),
                'bills' => \DB::table('bill_perms')->get(),
            ]);
        }

        return $this->permissions;
    }

    public function byUser(): Collection
    {
        if (is_null($this->permissionsByUser)) {
            $this->permissionsByUser = new Collection([
                'devices' => $this->getPermissions()->get('devices')->groupBy('user_id')->map->pluck('device_id'),
                'ports' => $this->getPermissions()->get('ports')->groupBy('user_id')->map->pluck('port_id'),
                'bills' => $this->getPermissions()->get('bills')->groupBy('user_id')->map->pluck('bill_id'),
            ]);
        }

        return $this->permissionsByUser;
    }

    public function byId(): Collection
    {
        if (is_null($this->permissionsById)) {
            $this->permissionsById = new Collection([
                'devices' => $this->getPermissions()->get('devices')->groupBy('device_id')->map->pluck('user_id'),
                'ports' => $this->getPermissions()->get('ports')->groupBy('port_id')->map->pluck('user_id'),
                'bills' => $this->getPermissions()->get('bills')->groupBy('bill_id')->map->pluck('user_id'),
            ]);
        }

        return $this->permissionsById;
    }

    public function forUser(int $user_id): Collection
    {
        return $this->byUser()->get($user_id, new Collection());
    }

    public function forDevice(int $device_id): Collection
    {
        return $this->byId()->get('devices')->get($device_id, new Collection());
    }

    public function forPort(int $port_id): Collection
    {
        return $this->byId()->get('ports')->get($port_id, new Collection());
    }

    public function forBill(int $bill_id): Collection
    {
        return $this->byId()->get('bills')->get($bill_id, new Collection());
    }
}
