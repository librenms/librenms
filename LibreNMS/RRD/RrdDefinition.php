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

use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidRrdTypeException;

class RrdDefinition
{
    private static $types = array('GAUGE', 'DERIVE', 'COUNTER', 'ABSOLUTE', 'DCOUNTER', 'DDERIVE');
    private $dataSets = array();

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
     * @return RrdDefinition
     */
    public function addDataset($name, $type, $min = null, $max = null, $heartbeat = null)
    {
        if (empty($name)) {
            d_echo("DS must be set to a non-empty string.");
        }

        $ds = array();
        $ds[] = $this->escapeName($name);
        $ds[] = $this->checkType($type);
        $ds[] = is_null($heartbeat) ? Config::get('rrd.heartbeat') : $heartbeat;
        $ds[] = is_null($min) ? 'U' : $min;
        $ds[] = is_null($max) ? 'U' : $max;

        $this->dataSets[] = $ds;

        return $this;
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
