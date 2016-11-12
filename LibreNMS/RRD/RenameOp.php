<?php
/**
 * RRDRenamer.php
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

namespace LibreNMS\RRD;

use Exception;
use LibreNMS\Exceptions\FileExistsException;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\RenameFailedException;

class RenameOp extends RRDOperation
{
    /**
     * @var callable
     */
    private $renameFunction;

    /**
     * RrdRenamer constructor.
     *
     * The function is passed the current filename without extension or path and $device
     * and should return the new filename without extension or path
     *
     * @param string $desc string description of this operation for display
     * @param string $search_pattern search pattern for files, uses php glob() patterns
     * @param callable $function The function that will return the new name of the file
     */
    public function __construct($desc, $search_pattern, $function)
    {
        parent::__construct($desc, $search_pattern);
        $this->renameFunction = $function;
    }

    /**
     * Run this rrd file rename operation
     */
    public function run()
    {
        $files = glob($this->getPattern());
        d_echo('Found ' . count($files) . " files.\n");

        foreach ($files as $file) {
            $device = $this->getDevice($file);

            $oldname = basename($file, '.rrd');
            $newname = call_user_func($this->renameFunction, $oldname, $device);

            $this->renameFile($device, $oldname, $newname);
        }
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
     * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
     *
     * @param array $device Device object
     * @param string $oldname RRD name array as used with rrd_name()
     * @param string $newname RRD name array as used with rrd_name()
     * @throws FileExistsException Destination file exists
     * @throws FileNotFoundException Source file does not exist
     * @throws RenameFailedException Rename operation failed
     */
    private function renameFile($device, $oldname, $newname)
    {
        $oldrrd = rrd_name($device['hostname'], $oldname);
        $newrrd = rrd_name($device['hostname'], $newname);

        if (!is_file($oldrrd)) {
            $msg = "$oldrrd not found";
            throw new FileNotFoundException($msg);
        }

        if (is_file($newrrd)) {
            $msg = "Destination file $newrrd exists";
            throw new FileExistsException($msg);
        }

        if (rename($oldrrd, $newrrd)) {
            echo " ${device['hostname']}: $oldrrd > $newrrd\n";
            log_event("Renamed $oldrrd to $newrrd", $device, "rrd_rename");
        } else {
            $msg = "Failed to rename $oldrrd to $newrrd";
            log_event($msg, $device, "rrd_rename");
            throw new RenameFailedException($msg);
        }
    }
}
