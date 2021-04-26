<?php
/**
 * Datastore.php
 *
 * Interface for datastores. Will be used to send them data through the put() method
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

namespace LibreNMS\Interfaces\Data;

interface Datastore
{
    /**
     * Check if this is enabled by the configuration
     *
     * @return bool
     */
    public static function isEnabled();

    /**
     * Checks if the datastore wants rrdtags to be sent when issuing put()
     *
     * @return bool
     */
    public function wantsRrdTags();

    /**
     * The name of this datastore
     *
     * @return string
     */
    public function getName();

    /**
     * Array of stats should be [type => [count => n, time => s]]
     *
     * @return array
     */
    public function getStats();

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
    public function put($device, $measurement, $tags, $fields);
}
