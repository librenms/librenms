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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use LibreNMS\Config;
use LibreNMS\Interfaces\Data\Datastore as DatastoreContract;

class Datastore
{
    protected $stores;

    /**
     * Initialize and create the Datastore(s)
     *
     * @param array $options
     * @return Datastore
     */
    public static function init($options = [])
    {
        $opts = [
            'r' => 'rrd.enable',
            'f' => 'influxdb.enable',
            'p' => 'prometheus.enable',
            'g' => 'graphite.enable',
        ];
        foreach ($opts as $opt => $setting) {
            if (isset($options[$opt])) {
                Config::set($setting, false);
            }
        }

        return app('Datastore');
    }

    public static function terminate()
    {
        \Rrd::close();
    }

    /**
     * Datastore constructor.
     * @param array $datastores Implement DatastoreInterface
     */
    public function __construct($datastores)
    {
        $this->stores = $datastores;
    }

    /**
     * Disable a datastore for the rest of this run
     *
     * @param string $name
     */
    public function disable($name)
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
     * Datastore-independent function which should be used for all polled metrics.
     *
     * RRD Tags:
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param array $device
     * @param string $measurement Name of this measurement
     * @param array $tags tags for the data (or to control rrdtool)
     * @param array|mixed $fields The data to update in an associative array, the order must be consistent with rrd_def,
     *                            single values are allowed and will be paired with $measurement
     */
    public function put($device, $measurement, $tags, $fields)
    {
        // convenience conversion to allow calling with a single value, so, e.g., these are equivalent:
        // data_update($device, 'mymeasurement', $tags, 1234);
        //     AND
        // data_update($device, 'mymeasurement', $tags, array('mymeasurement' => 1234));
        if (! is_array($fields)) {
            $fields = [$measurement => $fields];
        }

        foreach ($this->stores as $store) {
            /** @var DatastoreContract $store */
            // rrdtool_data_update() will only use the tags it deems relevant, so we pass all of them.
            // However, influxdb saves all tags, so we filter out the ones beginning with 'rrd_'.
            $temp_tags = $store->wantsRrdTags() ? $tags : $this->rrdTagFilter($tags);

            $store->put($device, $measurement, $temp_tags, $fields);
        }
    }

    /**
     * Filter all elements with keys that start with 'rrd_'
     *
     * @param array $arr input array
     * @return array Copy of $arr with all keys beginning with 'rrd_' removed.
     */
    private function rrdTagFilter($arr)
    {
        $result = [];
        foreach ($arr as $k => $v) {
            if (strpos($k, 'rrd_') === 0) {
                continue;
            }
            $result[$k] = $v;
        }

        return $result;
    }

    /**
     * Get all the active data stores
     *
     * @return array
     */
    public function getStores()
    {
        return $this->stores;
    }

    public function getStats()
    {
        return array_reduce($this->stores, function ($result, DatastoreContract $store) {
            $result[$store->getName()] = $store->getStats();

            return $result;
        }, []);
    }
}
