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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Interfaces\Data;

interface Datastore extends DataStorageInterface
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
}
