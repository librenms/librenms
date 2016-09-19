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

function setSnmpMock($oid, $value, $mib = null, $mibdir = null)
{
    global $mockSnmp;
    if (!isset($mockSnmp)) {
        $mockSnmp = array();
    }
    $num = snmp_translate_number($oid, $mib, $mibdir);
    $mockSnmp[$num] = $value;
}

function clearSnmpMock()
{
    global $mockSnmp;
    unset($mockSnmp);
}

function snmp_translate_number($oid, $mib = null, $mibdir = null)
{
    global $config;

    if (preg_match('/^[\.\d]*$/', $oid)) {
        return $oid;
    }

    $cmd = "snmptranslate -IR -On $oid";
    $cmd .= ' -M ' . (isset($mibdir) ? $mibdir : $config['mibdir']);
    if (isset($mib) && $mib) {
        $cmd .= " -m $mib";
    }

    $number = shell_exec($cmd);

    if (empty($number)) {
        throw new Exception('Could not translate oid: ' . $oid . PHP_EOL . 'Tried: ' . $cmd);
    }

    return trim($number);
}

function snmp_get($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    global $mockSnmp;
    if (isset($mockSnmp) && !empty($mockSnmp)) {
        $oid = snmp_translate_number($oid, $mib, $mibdir);

        if (isset($mockSnmp[$oid])) {
            return $mockSnmp[$oid];
        }
    }
    return false;
}

function snmp_walk($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    global $mockSnmp;
    if (!isset($mockSnmp)) {
        return false;
    }

    $output = '';
    $num = snmp_translate_number($oid, $mib, $mibdir);

    foreach ($mockSnmp as $key => $value) {
        if (starts_with($key, $num)) {
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
