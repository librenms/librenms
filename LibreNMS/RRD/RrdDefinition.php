<?php
/**
 * RrdDefinition.php
 *
 * Build a RRD definition.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\RRD;

use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidRrdTypeException;

class RrdDefinition
{
    private static $types = ['GAUGE', 'DERIVE', 'COUNTER', 'ABSOLUTE', 'DCOUNTER', 'DDERIVE'];
    private $dataSets = [];
    private $skipNameCheck = false;
    /**
     * @var array
     */
    private $source_files = [];

    /**
     * Make a new empty RrdDefinition
     */
    public static function make()
    {
        return new self();
    }

    /**
     * Add a dataset to this definition.
     * See https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html for more information.
     *
     * @param  string  $name  Textual name for this dataset. Must be [a-zA-Z0-9_], max length 19.
     * @param  string  $type  GAUGE | COUNTER | DERIVE | DCOUNTER | DDERIVE | ABSOLUTE.
     * @param  int  $min  Minimum allowed value.  null means undefined.
     * @param  int  $max  Maximum allowed value.  null means undefined.
     * @param  int  $heartbeat  Heartbeat for this dataset. Uses the global setting if null.
     * @param  string  $file  File containing source
     * @param  string  $source  Source DS inside source file
     * @return RrdDefinition
     */
    public function addDataset($name, $type, $min = null, $max = null, $heartbeat = null, $file = null, $source = null)
    {
        if (empty($name)) {
            d_echo('DS must be set to a non-empty string.');
        }

        $name = $this->escapeName($name);

        // migrate from source file
        if ($file && $source) {
            $source = $this->escapeName($source);

            // see if file is already in the file list, otherwise append at the next index
            $found = array_search($source, $this->source_files);
            $index = $found !== false ? $found : count($this->source_files);
            $this->source_files[$index] = $file;
            $name .= "=$source[$index]";
        }

        $this->dataSets[$name] = [
            $name,
            $this->checkType($type),
            is_null($heartbeat) ? Config::get('rrd.heartbeat') : $heartbeat,
            is_null($min) ? 'U' : $min,
            is_null($max) ? 'U' : $max,
        ];

        return $this;
    }

    /**
     * Get the RRD Definition as it would be passed to rrdtool
     *
     * @return string
     */
    public function __toString()
    {
        $initial = empty($this->source_files) ? '' : ('--source ' . implode(',', $this->source_files) . ' ');

        return array_reduce($this->dataSets, function ($carry, $ds) {
            return $carry . 'DS:' . implode(':', $ds) . ' ';
        }, $initial);
    }

    /**
     * Check if the give dataset name is valid for this definition
     *
     * @param  string  $name
     * @return bool
     */
    public function isValidDataset($name)
    {
        return $this->skipNameCheck || isset($this->dataSets[$this->escapeName($name)]);
    }

    /**
     * Disable checking if the name is valid for incoming data and just assign values
     * based on order
     *
     * @return $this
     */
    public function disableNameChecking()
    {
        $this->skipNameCheck = true;

        return $this;
    }

    /**
     * Check that the data set type is valid.
     *
     * @param  string  $type
     * @return mixed
     *
     * @throws InvalidRrdTypeException
     */
    private function checkType($type)
    {
        if (! in_array($type, self::$types)) {
            $msg = "$type is not valid, must be: " . implode(' | ', self::$types);
            throw new InvalidRrdTypeException($msg);
        }

        return $type;
    }

    /**
     * Remove all invalid characters from the name and truncate to 19 characters.
     *
     * @param  string  $name
     * @return string
     */
    private function escapeName($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);

        return substr($name, 0, 19);
    }
}
