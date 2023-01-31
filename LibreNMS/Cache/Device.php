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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Cache;

class Device
{
    /** @var \App\Models\Device[] */
    private array $devices = [];
    private ?int $primary = null;

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
     * @param  int  $device_id
     */
    public function setPrimary(int $device_id): void
    {
        $this->primary = $device_id;
    }

    /**
     * Check if a primary device is set
     */
    public function hasPrimary(): bool
    {
        return $this->primary !== null;
    }

    /**
     * Get a device by device_id or hostname
     *
     * @param  int|string|null  $device  device_id or hostname
     * @return \App\Models\Device
     */
    public function get(int|string|null $device): \App\Models\Device
    {
        if ($device === null) {
            return new \App\Models\Device;
        }

        // if string input is not an integer, get by hostname
        if (is_string($device) && ! ctype_digit($device)) {
            return $this->getByHostname($device);
        }

        // device is not be loaded, try to load it
        return $this->devices[$device] ?? $this->load($device);
    }

    /**
     * Get a device by hostname
     *
     * @param  string|null  $hostname
     * @return \App\Models\Device
     */
    public function getByHostname($hostname): \App\Models\Device
    {
        $device_id = array_column($this->devices, 'device_id', 'hostname')[$hostname] ?? 0;

        return $this->devices[$device_id] ?? $this->load($hostname, 'hostname');
    }

    /**
     * Ignore cache and load the device fresh from the database
     *
     * @param  int  $device_id
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
    public function flush(): void
    {
        $this->devices = [];
    }

    /**
     * Check if the device id is currently loaded into cache
     */
    public function has(int $device_id): bool
    {
        return isset($this->devices[$device_id]);
    }

    private function load(mixed $value, string $field = 'device_id'): \App\Models\Device
    {
        $device = \App\Models\Device::query()->where($field, $value)->first();

        if (! $device) {
            return new \App\Models\Device;
        }

        $this->devices[$device->device_id] = $device;

        return $device;
    }
}
