<?php
/**
 * RrdRenamer.php
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

namespace LibreNMS;

class RrdRenamer
{
    private $desc;
    private $pattern;
    /**
     * @var callable
     */
    private $renameFunction;

    /**
     * RrdRenamer constructor.
     *
     * The function is passed the arguments $device and the current filename without extension or path
     * and should return the new filename without extension or path
     *
     * @param string $desc string description of this operation for display
     * @param string $search_pattern search pattern for files, uses php glob() patterns
     * @param callable $function The function that will return the new name of the file
     */
    public function __construct($desc, $search_pattern, $function)
    {
        $this->desc = $desc;
        $this->pattern = $search_pattern;
        $this->renameFunction = $function;
    }

    /**
     * Run this rrd file rename operation
     *
     * @return bool returns if the operation was successful
     */
    public function run()
    {
        $files = glob($this->pattern);

        if (!empty($files)) {
            echo "Running: $this->desc\n";
        }

        foreach ($files as $file) {
            $device = $this->getDevice($file);

            $oldname = basename($file, '.rrd');
            $newname = call_user_func($this->renameFunction, $device, $oldname);

            echo " ${device['hostname']}: $oldname > $newname ";

            $success = rrd_file_rename($device, $oldname, $newname);

            echo $success ? 'Success' : 'Failed', PHP_EOL;
        }

        return true;
    }

    /**
     * Get the device array for the file we are working on
     *
     * @param string $filename path to the rrd file
     * @return array|null
     */
    private function getDevice($filename)
    {
        // this might be too simplistic, but complexity can be added if needed
        $host = basename(dirname($filename));
        return device_by_name($host);
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

