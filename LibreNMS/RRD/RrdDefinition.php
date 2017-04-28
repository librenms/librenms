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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\RRD;

use LibreNMS\Exceptions\InvalidRrdTypeException;

class RrdDefinition
{
    private static $types = array('GAUGE', 'DERIVE', 'COUNTER', 'ABSOLUTE', 'DCOUNTER', 'DDERIVE');
    private $dataSets = array();
    private $data = array();

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
     * @param string $name Textual name for this dataset. Must be [a-zA-Z0-9_], max length 19.
     * @param string $type GAUGE | COUNTER | DERIVE | DCOUNTER | DDERIVE | ABSOLUTE.
     * @param int $min Minimum allowed value.  null means undefined.
     * @param int $max Maximum allowed value.  null means undefined.
     * @param int $heartbeat Heartbeat for this dataset. Uses the global setting if null.
     * @return $this
     */
    public function addDataset($name, $type, $min = null, $max = null, $heartbeat = null)
    {
        global $config;

        if (empty($name)) {
            d_echo("DS must be set to a non-empty string.");
        }
        $escaped_name = $this->escapeName($name);

        $ds = array();
        $ds[] = $escaped_name;
        $ds[] = $this->checkType($type);
        $ds[] = is_null($heartbeat) ? $config['rrd']['heartbeat'] : $heartbeat;
        $ds[] = is_null($min) ? 'U' : $min;
        $ds[] = is_null($max) ? 'U' : $max;

        $this->dataSets[] = $ds;
        $this->data[$escaped_name] = 'U'; // Initialize the data array as undefined data

        return $this;
    }

    /**
     * Set a single data value.
     * $dsname may be the index of the dataset, but this is not recommended
     *
     * @param string|int $dsname The name of the dataset to set the value for
     * @param int|null $value value
     * @return bool
     */
    public function setValue($dsname, $value)
    {
        // value sanity checks
        if (is_numeric($value)) {
            $value = $value + 0;
        } else {
            if (!is_null($value)) {
                d_echo("Error: Value must be numeric or null! Given: $value");
            }
            $value = 'U';
        }

        // check and set string based keys
        $escaped_name = $this->escapeName($dsname);
        if (isset($this->data[$escaped_name])) {
            $this->data[$escaped_name] = $value;
            return true;
        }

        // check and set numeric based keys
        if (is_numeric($dsname) && isset($this->dataSets[$dsname])) {
            $this->data[$this->dataSets[$dsname][0]] = $value;
            return true;
        }

        d_echo("Invalid dsname: $dsname");
        return false;
    }

    /**
     * Set an array of data values
     * If using a numeric dsname index, it is recommended to set all data.
     * Using a string dsname index allows for partial data sets.
     *
     * @param array $data An array with keys of the dsname and values of the data value
     */
    public function setData($data)
    {
        // if a numeric, sequential array is give match it up with the same order as dsnames
        if (array_keys($data) === array_keys($this->dataSets)) {
            $data = array_combine($this->getDsNames(), $data);
        }

        foreach ($data as $dsname => $value) {
            $this->setValue($dsname, $value);
        }
    }

    /**
     * Get an array of data to send to the data_update() function
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function getDsNames()
    {
        return array_column($this->dataSets, 0);
    }

    /**
     * Get the RRD Definition as it would be passed to rrdtool
     *
     * @return string
     */
    public function __toString()
    {
        return array_reduce($this->dataSets, function ($carry, $ds) {
            return $carry . 'DS:' . implode(':', $ds) . ' ';
        }, '');
    }

    /**
     * Check that the data set type is valid.
     *
     * @param string $type
     * @return mixed
     * @throws InvalidRrdTypeException
     */
    private function checkType($type)
    {
        if (!in_array($type, self::$types)) {
            $msg = "$type is not valid, must be: " . implode(' | ', self::$types);
            throw new InvalidRrdTypeException($msg);
        }
        return $type;
    }

    /**
     * Remove all invalid characters from the name and truncate to 19 characters.
     *
     * @param string $name
     * @return string
     */
    private function escapeName($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        return substr($name, 0, 19);
    }
}
