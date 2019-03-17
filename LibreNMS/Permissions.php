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

namespace LibreNMS;

use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Auth;
use DB;

class Permissions
{
    private $devicePermissions;
    private $portPermissions;
    private $billPermissions;

    /**
     * @param Device|int $device
     * @param User|int $user
     * @return boolean
     */
    public function canAccessDevice($device, $user = null)
    {
        return $this->getDevicePermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('device_id', $this->getDeviceId($device))
            ->isNotEmpty();
    }

    /**
     * @param Port|int $port
     * @param User|int $user
     * @return boolean
     */
    public function canAccessPort($port, $user = null)
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('port_id', $this->getPortId($port))
            ->isNotEmpty();
    }

    /**
     * @param Bill|int $bill
     * @param User|int $user
     * @return boolean
     */
    public function canAccessBill($bill, $user = null)
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->where('bill_id', $this->getBillId($bill))
            ->isNotEmpty();
    }

    public function usersForDevice($device)
    {
        return $this->getDevicePermissions()
            ->where('device_id', $this->getDeviceId($device))
            ->pluck('user_id');
    }

    public function usersForPort($port)
    {
        return $this->getPortPermissions()
            ->where('port_id', $this->getPortId($port))
            ->pluck('user_id');
    }

    public function usersForBill($bill)
    {
        return $this->getBillPermissions()
            ->where('bill_id', $this->getBillId($bill))
            ->pluck('user_id');
    }

    public function devicesForUser($user = null)
    {
        return $this->getDevicePermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('device_id');
    }

    public function portsForUser($user = null)
    {
        return $this->getPortPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('port_id');
    }

    public function billsForUser($user = null)
    {
        return $this->getBillPermissions()
            ->where('user_id', $this->getUserId($user))
            ->pluck('bill_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDevicePermissions()
    {
        if (is_null($this->devicePermissions)) {
            $this->devicePermissions = DB::table('devices_perms')->get();
        }

        return $this->devicePermissions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getPortPermissions()
    {
        if (is_null($this->portPermissions)) {
            $this->portPermissions = DB::table('ports_perms')->get();
        }

        return $this->portPermissions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getBillPermissions()
    {
        if (is_null($this->billPermissions)) {
            $this->billPermissions = DB::table('bill_ports')->get();
        }

        return $this->billPermissions;
    }

    /**
     * @param $user
     * @return int|null
     */
    private function getUserId($user)
    {
        return $user instanceof User ? $user->user_id : (is_int($user) ? $user : Auth::id());
    }

    /**
     * @param $device
     * @return int
     */
    private function getDeviceId($device)
    {
        return $device instanceof Device ? $device->device_id : (is_int($device) ? $device : 0);
    }

    /**
     * @param $port
     * @return int
     */
    private function getPortId($port)
    {
        return $port instanceof Port ? $port->port_id : (is_int($port) ? $port : 0);
    }

    /**
     * @param $bill
     * @return int
     */
    private function getBillId($bill)
    {
        return $bill instanceof Bill ? $bill->bill_id : (is_int($bill) ? $bill : 0);
    }
}
