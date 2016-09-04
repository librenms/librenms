<?php
/**
 * mock.snmp.inc.php
 *
 * Mock functions from includes/snmp.inc.php to allow tests to run without real snmp
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

function setSnmpMock($mockSnmpArray)
{
    global $mockSnmp;
    $mockSnmp = $mockSnmpArray;
}

function snmp_get($device, $oid)
{
    global $mockSnmp;
    if (isset($mockSnmp) && !empty($mockSnmp)) {
        if (isset($mockSnmp[$oid])) {
            return $mockSnmp[$oid];
        }
    }
    return false;
}

function snmp_walk($device, $oid)
{
    global $mockSnmp;
    $output = '';
    foreach ($mockSnmp as $key => $value) {
        if (starts_with($key, $oid)) {
            $output .= $value . PHP_EOL;
        }
    }

    if (empty($output)) {
        // does this match the behavior of the real snmp_walk()?
        return false;
    } else {
        return $output;
    }
}

function register_mibs()
{
    // stub
}
