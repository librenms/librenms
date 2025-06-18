<?php

/**
 * Datastore.php
 *
 * Aggregates all enabled datastores and dispatches data to them
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Config;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Data\Datastore as DatastoreContract;
use LibreNMS\Interfaces\Data\WriteInterface;

class Datastore implements WriteInterface, DataStorageInterface
{
    /**
     * @var DatastoreContract[]
     */
    protected array $stores;

    /**
     * Legacy factory method to initialize and create the Datastore(s)
     *
     * @param  array  $options
     * @return Datastore
     */
    public static function init(array $options = []): Datastore
    {
        $opts = [
            'r' => 'rrd.enable',
            'f' => 'influxdb.enable',
            'p' => 'prometheus.enable',
            'g' => 'graphite.enable',
            '2' => 'influxdbv2.enable',
        ];
        foreach ($opts as $opt => $setting) {
            if (isset($options[$opt])) {
                Config::set($setting, false);
            }
        }

        return app('Datastore');
    }

    /**
     * Datastore constructor.
     *
     * @param  DatastoreContract[]  $datastores
     */
    public function __construct(array $datastores)
    {
        $this->stores = $datastores;
    }

    public function terminate(): void
    {
        foreach ($this->stores as $store) {
            $store->terminate();
        }
    }

    /**
     * Disable a datastore for the rest of this run
     *
     * @param  string  $name
     */
    public function disable(string $name): void
    {
        $store = app("LibreNMS\\Data\\Store\\$name");
        $position = array_search($store, $this->stores);
        if ($position !== false) {
            c_echo("[%g$name Disabled%n]\n");
            unset($this->stores[$position]);
        } else {
            c_echo("[%g$name is not a valid datastore name%n]\n");
        }
    }

    /**
     * Compatability method to support old writes
     *
     * RRD Tags:
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param  array  $device
     * @param  string  $measurement  Name of this measurement
     * @param  array  $tags  tags for the data (or to control rrdtool)
     * @param  array|mixed  $fields  The data to update in an associative array, the order must be consistent with rrd_def,
     *                               single values are allowed and will be paired with $measurement
     */
    public function put($device, $measurement, $tags, $fields): void
    {
        // convenience conversion to allow calling with a single value, so, e.g., these are equivalent:
        // put($device, 'mymeasurement', $tags, 1234);
        //     AND
        // put($device, 'mymeasurement', $tags, array('mymeasurement' => 1234));
        if (! is_array($fields)) {
            $fields = [$measurement => $fields];
        }

        // get the rrd meta data out of the tags array
        $meta = $this->rrdTagFilter($tags);

        // set device if it is not the primary device
        if (is_array($device) && isset($device['device_id']) && $device['device_id'] !== DeviceCache::getPrimary()->device_id) {
            $meta['device'] = DeviceCache::get($device['device_id']);
        } elseif ($device instanceof Device && $device->device_id !== DeviceCache::getPrimary()->device_id) {
            $meta['device'] = $device;
        }

        $this->write($measurement, $tags, $fields, $meta);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $tags, array $fields, array $meta = []): void
    {
        foreach ($this->stores as $store) {
            $store->write($measurement, $fields, $tags, $meta);
        }
    }

    /**
     * Filter all elements with keys that start with 'rrd_'
     *
     * @param  array<string, mixed>  $tags  input array
     * @return array<string, mixed> Copy of $arr with all keys beginning with 'rrd_' removed.
     */
    private function rrdTagFilter(array &$tags): array
    {
        [$metaTags, $filteredTags] = Arr::partition($tags, function ($value, string $tag) {
            return str_starts_with($tag, 'rrd_');
        });

        $tags = $filteredTags; // Update the original array with remaining tags

        return $metaTags;
    }

    /**
     * Get all the active data stores
     *
     * @return DatastoreContract[]
     */
    public function getStores(): array
    {
        return $this->stores;
    }

    /**
     * Get the measurements for all datastores, keyed by datastore name
     *
     * @return Collection
     */
    public function getStats(): Collection
    {
        return collect($this->stores)->mapWithKeys(function (DatastoreContract $store) {
            return [$store->getName() => $store->getStats()];
        });
    }
}
