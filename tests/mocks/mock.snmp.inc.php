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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use Illuminate\Support\Str;
use LibreNMS\Config;

$snmpMockCache = [];

/**
 * Cache the data from an snmprec file
 * in ./tests/snmpsim/
 *
 * @param string $file the snmprec file name (excluding .snmprec)
 */
function cache_snmprec($file)
{
    global $snmpMockCache;
    if (isset($snmpMockCache[$file])) {
        return;
    }
    $snmpMockCache[$file] = [];

    $data = file_get_contents(Config::get('install_dir') . "/tests/snmpsim/$file.snmprec");
    $line = strtok($data, "\r\n");
    while ($line !== false) {
        [$oid, $type, $data] = explode('|', $line, 3);
        if ($type == '4') {
            $data = trim($data);
        } elseif ($type == '6') {
            $data = trim($data, '.');
        } elseif ($type == '4x') {
            // MacAddress type is stored as hex string, but we don't understand mibs
            if (Str::startsWith($oid, [
                '1.3.6.1.2.1.2.2.1.6', // IF-MIB::ifPhysAddress
                '1.3.6.1.2.1.17.1.1.0', // BRIDGE-MIB::dot1dBaseBridgeAddress.0
                '1.3.6.1.4.1.890.1.5.13.13.8.1.1.20', // IES5206-MIB::slotModuleMacAddress
            ])) {
                $data = \LibreNMS\Util\Rewrite::readableMac($data);
            } else {
                $data = hex2str($data);
            }
        }

        $snmpMockCache[$file][$oid] = [$type, $data];
        $line = strtok("\r\n");
    }
}

/**
 * Get all data of the specified $community from the snmprec cache
 *
 * @param string $community snmp community to return
 * @return array array of the data containing: [$oid][$type, $data]
 * @throws Exception this $community is not cached
 */
function snmprec_get($community)
{
    global $snmpMockCache;
    cache_snmprec($community);
    d_echo($snmpMockCache);

    if (isset($snmpMockCache[$community])) {
        return $snmpMockCache[$community];
    }

    throw new Exception("SNMPREC: community $community not cached");
}

/**
 * Get an $oid from the specified $community
 *
 * @param string $community the community to fetch data from
 * @param string $oid numeric oid of data to fetch
 * @return array array of the data containing: [$type, $data]
 * @throws Exception this $oid is not cached
 */
function snmprec_get_oid($community, $oid)
{
    global $snmpMockCache;
    cache_snmprec($community);

    if (isset($snmpMockCache[$community]) && isset($snmpMockCache[$community][$oid])) {
        return $snmpMockCache[$community][$oid];
    }

    throw new Exception("SNMPREC: oid $community:$oid not cached");
}

/**
 * Get the numeric oid of an oid
 * The leading dot is ommited by default to be compatible with snmpsim
 *
 * @param string $oid the oid to tranlslate
 * @param string $mib mib to use
 * @param string $mibdir mib dir to look for mib in
 * @return string the oid in numeric format (.1.3.4.5)
 * @throws Exception Could not translate the oid
 */
function snmp_translate_number($oid, $mib = null, $mibdir = null)
{
    // optimizations (35s -> 1.6s on my laptop)
    if ($oid == 'SNMPv2-MIB::sysDescr.0') {
        return '1.3.6.1.2.1.1.1.0';
    }
    if ($oid == 'SNMPv2-MIB::sysObjectID.0') {
        return '1.3.6.1.2.1.1.2.0';
    }
    if ($oid == 'ENTITY-MIB::entPhysicalDescr.1') {
        return '1.3.6.1.2.1.47.1.1.1.1.2.1';
    }
    if ($oid == 'SML-MIB::product-Name.0') {
        return '1.3.6.1.4.1.2.6.182.3.3.1.0';
    }
    if ($oid == 'ENTITY-MIB::entPhysicalMfgName.1') {
        return '1.3.6.1.2.1.47.1.1.1.1.12.1';
    }
    if ($oid == 'GAMATRONIC-MIB::psUnitManufacture.0') {
        return '1.3.6.1.4.1.6050.1.1.2.0';
    }
    if ($oid === 'SYNOLOGY-SYSTEM-MIB::systemStatus.0') {
        return '1.3.6.1.4.1.6574.1.1.0';
    }
    // end optimizations

    if (preg_match('/^[\.\d]*$/', $oid)) {
        return ltrim($oid, '.');
    }

    $cmd = "snmptranslate -IR -On '$oid'";
    $cmd .= ' -M ' . (isset($mibdir) ? Config::get('mib_dir') . ':' . Config::get('mib_dir') . "/$mibdir" : Config::get('mib_dir'));
    if (isset($mib) && $mib) {
        $cmd .= " -m $mib";
    }

    $number = shell_exec($cmd);

    if (empty($number)) {
        throw new Exception('Could not translate oid: ' . $oid . PHP_EOL . 'Tried: ' . $cmd);
    }

    return trim($number, ". \n\r");
}

function snmp_translate_type($oid, $mib = null, $mibdir = null)
{
    $cmd = "snmptranslate -IR -Td $oid";
    $cmd .= ' -M ' . (isset($mibdir) ? Config::get('mib_dir') . ':' . Config::get('mib_dir') . "/$mibdir" : Config::get('mib_dir'));
    if (isset($mib) && $mib) {
        $cmd .= " -m $mib";
    }

    $result = shell_exec($cmd);

    if (empty($result)) {
        throw new Exception('Could not translate oid: ' . $oid . PHP_EOL . 'Tried: ' . $cmd);
    }

    if (Str::contains($result, 'OCTET STRING')) {
        return 4;
    }
    if (Str::contains($result, 'Integer32')) {
        return 2;
    }
    if (Str::contains($result, 'NULL')) {
        return 5;
    }
    if (Str::contains($result, 'OBJECT IDENTIFIER')) {
        return 6;
    }
    if (Str::contains($result, 'IpAddress')) {
        return 64;
    }
    if (Str::contains($result, 'Counter32')) {
        return 65;
    }
    if (Str::contains($result, 'Gauge32')) {
        return 66;
    }
    if (Str::contains($result, 'TimeTicks')) {
        return 67;
    }
    if (Str::contains($result, 'Opaque')) {
        return 68;
    }
    if (Str::contains($result, 'Counter64')) {
        return 70;
    }

    throw new Exception('Unknown type');
}

// Mocked functions

function snmp_get($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $community = $device['community'];
    $num_oid = snmp_translate_number($oid, $mib, $mibdir);

    try {
        $data = snmprec_get_oid($community, $num_oid);

        $result = $data[1];
        if ($data[0] == 6) {
            $result = '.' . $data[1];
        }

        d_echo("[SNMP] snmpget $community $oid ($num_oid): $result\n");

        return $result;
    } catch (Exception $e) {
        d_echo("[SNMP] snmpget $community $oid ($num_oid): no data\n");

        return false;
    }
}

function snmp_get_multi_oid($device, $oids, $options = '-OUQn', $mib = null, $mibdir = null)
{
    if (! is_array($oids)) {
        $oids = explode(' ', $oids);
    }

    $data = [];
    foreach ($oids as $index => $oid) {
        if (Str::contains($options, 'n')) {
            $oid_name = '.' . snmp_translate_number($oid, $mib, $mibdir);
            $val = snmp_get($device, $oid_name, $options, $mib, $mibdir);
        } elseif (Str::contains($options, 's') && Str::contains($oid, '::')) {
            $tmp = explode('::', $oid);
            $oid_name = $tmp[1];
            $val = snmp_get($device, $oid, $options, $mib, $mibdir);
        } else {
            $oid_name = $oid;
            $val = snmp_get($device, $oid, $options, $mib, $mibdir);
        }

        if ($val !== false) {
            $data[$oid_name] = $val;
        }
    }

    return $data;
}

function snmp_walk($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $community = $device['community'];
    $dev = snmprec_get($community);
    $num_oid = snmp_translate_number($oid, $mib, $mibdir);

    $output = '';
    foreach ($dev as $key => $data) {
        if (Str::startsWith($key, $num_oid)) {
            if ($data[0] == 6) {
                $output .= '.' . $data[1] . PHP_EOL;
            } else {
                $output .= $data[1] . PHP_EOL;
            }
        }
    }

    d_echo("[SNMP] snmpwalk $community $num_oid: ");
    if (empty($output)) {
        d_echo("no data\n");
        // does this match the behavior of the real snmp_walk()?
        return false;
    } else {
        d_echo($output);

        return $output;
    }
}
