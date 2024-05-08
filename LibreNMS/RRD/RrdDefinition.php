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
    private $sources = [];
    private $invalid_source = [];
    private $skipNameCheck = false;

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
     * @param  string  $source_ds  Dataset to copy data from an existing rrd file
     * @param  string  $source_file  File to copy data from (may be ommitted copy from the current file)
     * @return RrdDefinition
     */
    public function addDataset($name, $type, $min = null, $max = null, $heartbeat = null, $source_ds = null, $source_file = null)
    {
        if (empty($name)) {
            d_echo('DS must be set to a non-empty string.');
        }

        $name = $this->escapeName($name);
        $this->dataSets[$name] = [
            'name' => $name,
            'type' => $this->checkType($type),
            'hb' => is_null($heartbeat) ? Config::get('rrd.heartbeat') : $heartbeat,
            'min' => is_null($min) ? 'U' : $min,
            'max' => is_null($max) ? 'U' : $max,
            'source_ds' => $source_ds,
            'source_file' => $source_file,
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
        $def = array_reduce($this->dataSets, function ($carry, $ds) {
            $name = $ds['name'] . $this->createSource($ds['source_ds'], $ds['source_file']);

            return $carry . "DS:$name:{$ds['type']}:{$ds['hb']}:{$ds['min']}:{$ds['max']} ";
        }, '');
        $sources = implode(' ', array_map(fn ($s) => "--source $s ", $this->sources));

        return $sources . $def;
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

    private function createSource(?string $ds, ?string $file): string
    {
        if (empty($ds)) {
            return '';
        }

        $output = '=' . $ds;

        // if is file given, find or add it to the sources list
        if ($file) {
            $index = array_search($file, $this->sources);
            if ($index === false) {
                // check if source rrd exists and cache failures
                // using file_exists because source does not seem to support rrdcached
                // so this will only work if we have file access to the old rrd file
                if (isset($this->invalid_source[$file]) || ! file_exists($file)) {
                    $this->invalid_source[$file] = true;

                    return ''; // skip source if file does not exist
                }

                $this->sources[] = $file;
                end($this->sources);
                $index = key($this->sources);
            }

            $output .= '[' . ($index + 1) . ']'; // rrdcreate sources are 1 based
        }

        return $output;
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
