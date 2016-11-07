<?php
/**
 * RRDOperation.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\RRD;

abstract class RRDOperation
{
    private $desc;
    private $pattern;

    public function __construct($desc, $search_pattern)
    {
        $this->desc = $desc;
        $this->pattern = $search_pattern;
    }

    /**
     * Run this rrd file operation
     */
    abstract public function run();

    /**
     * @return string the description of this operation
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Get the glob() pattern that searches for files for this rename operation
     *
     * @param array $device If supplied, will return the pattern under a specific device
     * @return string returns the full path glob() pattern
     */
    public function getPattern($device = null)
    {
        global $config;
        $hostname = isset($device) ? $device['hostname'] : '*';

        return "${config['rrd_dir']}/$hostname/$this->pattern.rrd";
    }
}
