<?php
/**
 * rrdtool.inc.php
 *
 * Helper for processing rrdtool requests efficiently
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
 * @link       http://librenms.org
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;

/**
 * Generates a graph file at $graph_file using $options
 * Opens its own rrdtool pipe.
 *
 * @param string $graph_file
 * @param string $options
 * @return int
 */
function rrdtool_graph($graph_file, $options)
{
    return Rrd::graph($graph_file, $options);
}

/**
 * Checks if the rrd file exists on the server
 * This will perform a remote check if using rrdcached and rrdtool >= 1.5
 *
 * @param string $filename full path to the rrd file
 * @return bool whether or not the passed rrd file exists
 */
function rrdtool_check_rrd_exists($filename)
{
    return Rrd::checkRrdExists($filename);
}

/**
 * Escapes strings for RRDtool
 *
 * @param string $string the string to escape
 * @param int $length if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
 * @return string
 */
function rrdtool_escape($string, $length = null)
{
    $result = shorten_interface_type($string);
    $result = str_replace("'", '', $result);            // remove quotes

    if (is_numeric($length)) {
        // preserve original $length for str_pad()

        // determine correct strlen() for substr_count()
        $string_length = strlen($string);
        $substr_count_length = $length;

        if ($length > $string_length) {
            $substr_count_length = $string_length; // If $length is greater than the haystack length, then substr_count() will produce a warning; fix warnings.
        }

        $extra = substr_count($string, ':', 0, $substr_count_length);
        $result = substr(str_pad($result, $length), 0, ($length + $extra));
        if ($extra > 0) {
            $result = substr($result, 0, (-1 * $extra));
        }
    }

    $result = str_replace(':', '\:', $result);          // escape colons

    return $result . ' ';
} // rrdtool_escape

/**
 * Generates a filename based on the hostname (or IP) and some extra items
 *
 * @param string $host Host name
 * @param array|string $extra Components of RRD filename - will be separated with "-", or a pre-formed rrdname
 * @param string $extension File extension (default is .rrd)
 * @return string the name of the rrd file for $host's $extra component
 */
function rrd_name($host, $extra, $extension = '.rrd')
{
    return Rrd::name($host, $extra, $extension);
} // rrd_name

/**
 * Generates a path based on the hostname (or IP)
 *
 * @param string $host Host name
 * @return string the name of the rrd directory for $host
 */
function get_rrd_dir($host)
{
    $host = str_replace(':', '_', trim($host, '[]'));

    return implode('/', [Config::get('rrd_dir'), $host]);
} // rrd_dir

/**
 * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
 *
 * @param array $device Device object
 * @param string|array $oldname RRD name array as used with rrd_name()
 * @param string|array $newname RRD name array as used with rrd_name()
 * @return bool indicating rename success or failure
 */
function rrd_file_rename($device, $oldname, $newname)
{
    return Rrd::renameFile($device, $oldname, $newname);
} // rrd_file_rename
