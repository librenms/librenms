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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
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
     * @return \App\Models\Device
     */
    public function getPrimary(): \App\Models\Device
    {
        return $this->get($this->primary);
    }

    /**
     * Set the primary device.
     * This will be fetched by getPrimary()
     *
     * @param int $device_id
     */
    public function setPrimary(int $device_id)
    {
        $this->primary = $device_id;
    }

    /**
     * Get a device by device_id
     *
     * @param int $device_id
     * @return \App\Models\Device
     */
    public function get(?int $device_id): \App\Models\Device
    {
        if (! is_null($device_id) && ! array_key_exists($device_id, $this->devices)) {
            return $this->load($device_id);
        }

        return $this->devices[$device_id] ?? new \App\Models\Device;
    }

    /**
     * Get a device by hostname
     *
     * @param string $hostname
     * @return \App\Models\Device
     */
    public function getByHostname($hostname): \App\Models\Device
    {
        $device_id = collect($this->devices)->pluck('device_id', 'hostname')->get($hostname);

        if (! $device_id) {
            return $this->load($hostname, 'hostname');
        }

        return $this->devices[$device_id] ?? new \App\Models\Device;
    }

    /**
     * Ignore cache and load the device fresh from the database
     *
     * @param int $device_id
     * @return \App\Models\Device
     */
    public function refresh(?int $device_id): \App\Models\Device
    {
        unset($this->devices[$device_id]);

        return $this->get($device_id);
    }

    /**
     * Flush the cache
     */
    public function flush()
    {
        $this->devices = [];
    }

    private function load($value, $field = 'device_id')
    {
        $device = \App\Models\Device::query()->where($field, $value)->first();

        if (! $device) {
            return new \App\Models\Device;
        }

        $this->devices[$device->device_id] = $device;

        return $device;
    }
}
