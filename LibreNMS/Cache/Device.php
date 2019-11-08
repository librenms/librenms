<?php
/**
 * Device.php
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

namespace LibreNMS\Cache;

class Device
{
    private $devices = [];
    private $primary;

    public function getPrimary(): \App\Models\Device
    {
        return self::get($this->primary);
    }

    public function setPrimary(int $device_id)
    {
        $this->primary = $device_id;
    }

    public function get(int $device_id): \App\Models\Device
    {
        if (!array_key_exists($device_id, $this->devices)) {
            self::load($device_id);
        }

        return $this->devices[$device_id];
    }

    public function refresh(int $device_id) : \App\Models\Device
    {
        unset($this->devices[$device_id]);
        return self::get($device_id);
    }

    public function all() : array
    {
        return $this->devices;
    }

    private function load(int $device_id)
    {
        $device = \App\Models\Device::find($device_id);
        $device->loadOs();

        $this->devices[$device_id] = $device;
    }
}
