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

    /**
     * Gets the current primary device.
     *
     * @return \App\Models\Device|null
     */
    public function getPrimary()
    {
        return self::get($this->primary);
    }

    /**
     * Set the primary device.
     * This will be fetched by getPrimary()
     *
     * @param int $device_id
     */
    public function setPrimary($device_id)
    {
        $this->primary = $device_id;
    }

    /**
     * Get a device by device_id
     *
     * @param int $device_id
     * @return \App\Models\Device|null
     */
    public function get($device_id)
    {
        if (!array_key_exists($device_id, $this->devices)) {
            return self::load($device_id);
        }

        return $this->devices[$device_id];
    }

    /**
     * Get a device by hostname
     *
     * @param string $hostname
     * @return \App\Models\Device|null
     */
    public function getByHostname($hostname)
    {
        $device_id = $device_id = collect($this->devices)->pluck('device_id', 'hostname')->get($hostname);

        if (!$device_id) {
            return $this->load($hostname, 'hostname');
        }

        return $this->devices[$device_id];
    }

    /**
     * Ignore cache and load the device fresh from the database
     *
     * @param int $device_id
     * @return \App\Models\Device|null
     */
    public function refresh($device_id)
    {
        unset($this->devices[$device_id]);
        return self::get($device_id);
    }

    private function load($value, $field = 'device_id')
    {
        $device = \App\Models\Device::query()->where($field, $value)->first();

        if ($device) {
            $device->loadOs();
            $this->devices[$device->device_id] = $device;
        }

        return $device;
    }
}
