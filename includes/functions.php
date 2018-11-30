<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage functions
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

use Illuminate\Database\Events\QueryExecuted;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\HostUnreachablePingException;
use LibreNMS\Exceptions\InvalidPortAssocModeException;
use LibreNMS\Exceptions\LockException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Util\MemcacheLock;
use Symfony\Component\Process\Process;

/**
 * Set debugging output
 *
 * @param bool $state If debug is enabled or not
 * @param bool $silence When not debugging, silence every php error
 * @return bool
 */
function set_debug($state = true, $silence = false)
{
    global $debug;

    $debug = $state; // set to global

    restore_error_handler(); // disable Laravel error handler

    if (isset($debug) && $debug) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('log_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE);

        \LibreNMS\Util\Laravel::enableCliDebugOutput();
        \LibreNMS\Util\Laravel::enableQueryDebug();
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 1);
        error_reporting($silence ? 0 : E_ERROR);

        \LibreNMS\Util\Laravel::disableCliDebugOutput();
        \LibreNMS\Util\Laravel::disableQueryDebug();
    }

    return $debug;
}//end set_debug()

function array_sort_by_column($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
}

function mac_clean_to_readable($mac)
{
    return rtrim(chunk_split($mac, 2, ':'), ':');
}

function only_alphanumeric($string)
{
    return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

/**
 * Parse cli discovery or poller modules and set config for this run
 *
 * @param string $type discovery or poller
 * @param array $options get_opts array (only m key is checked)
 * @return bool
 */
function parse_modules($type, $options)
{
    $override = false;

    if ($options['m']) {
        Config::set("{$type}_modules", []);
        foreach (explode(',', $options['m']) as $module) {
            // parse submodules (only supported by some modules)
            if (str_contains($module, '/')) {
                list($module, $submodule) = explode('/', $module, 2);
                $existing_submodules = Config::get("{$type}_submodules.$module", []);
                $existing_submodules[] = $submodule;
                Config::set("{$type}_submodules.$module", $existing_submodules);
            }

            $dir = $type == 'poller' ? 'polling' : $type;
            if (is_file("includes/$dir/$module.inc.php")) {
                Config::set("{$type}_modules.$module", 1);
                $override = true;
            }
        }

        // display selected modules
        $modules = array_map(function ($module) use ($type) {
            $submodules = Config::get("{$type}_submodules.$module");
            return $module . ($submodules ? '(' . implode(',', $submodules) . ')' : '');
        }, array_keys(Config::get("{$type}_modules", [])));

        d_echo("Override $type modules: " . implode(', ', $modules) . PHP_EOL);
    }

    return $override;
}

function logfile($string)
{
    global $config;

    $fd = fopen($config['log_file'], 'a');
    fputs($fd, $string . "\n");
    fclose($fd);
}

/**
 * Detect the os of the given device.
 *
 * @param array $device device to check
 * @return string the name of the os
 */
function getHostOS($device)
{
    $device['sysDescr']    = snmp_get($device, "SNMPv2-MIB::sysDescr.0", "-Ovq");
    $device['sysObjectID'] = snmp_get($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");

    d_echo("| {$device['sysDescr']} | {$device['sysObjectID']} | \n");

    $deferred_os = array(
        'freebsd',
        'linux',
    );

    // check yaml files
    $os_defs = Config::get('os');
    foreach ($os_defs as $os => $def) {
        if (isset($def['discovery']) && !in_array($os, $deferred_os)) {
            foreach ($def['discovery'] as $item) {
                if (checkDiscovery($device, $item)) {
                    return $os;
                }
            }
        }
    }

    // check include files
    $os = null;
    $pattern = Config::get('install_dir') . '/includes/discovery/os/*.inc.php';
    foreach (glob($pattern) as $file) {
        include $file;
        if (isset($os)) {
            return $os;
        }
    }

    // check deferred os
    foreach ($deferred_os as $os) {
        if (isset($os_defs[$os]['discovery'])) {
            foreach ($os_defs[$os]['discovery'] as $item) {
                if (checkDiscovery($device, $item)) {
                    return $os;
                }
            }
        }
    }

    return 'generic';
}

/**
 * Check an array of conditions if all match, return true
 * sysObjectID if sysObjectID starts with any of the values under this item
 * sysDescr if sysDescr contains any of the values under this item
 * sysDescr_regex if sysDescr matches any of the regexes under this item
 * snmpget perform an snmpget on `oid` and check if the result contains `value`. Other subkeys: options, mib, mibdir
 *
 * Appending _except to any condition will invert the match.
 *
 * @param array $device
 * @param array $array Array of items, keys should be sysObjectID, sysDescr, or sysDescr_regex
 * @return bool the result (all items passed return true)
 */
function checkDiscovery($device, $array)
{
    // all items must be true
    foreach ($array as $key => $value) {
        if ($check = ends_with($key, '_except')) {
            $key = substr($key, 0, -7);
        }

        if ($key == 'sysObjectID') {
            if (starts_with($device['sysObjectID'], $value) == $check) {
                return false;
            }
        } elseif ($key == 'sysDescr') {
            if (str_contains($device['sysDescr'], $value) == $check) {
                return false;
            }
        } elseif ($key == 'sysDescr_regex') {
            if (preg_match_any($device['sysDescr'], $value) == $check) {
                return false;
            }
        } elseif ($key == 'sysObjectID_regex') {
            if (preg_match_any($device['sysObjectID'], $value) == $check) {
                return false;
            }
        } elseif ($key == 'snmpget') {
            $options = isset($value['options']) ? $value['options'] : '-Oqv';
            $mib = isset($value['mib']) ? $value['mib'] : null;
            $mib_dir = isset($value['mib_dir']) ? $value['mib_dir'] : null;
            $op = isset($value['op']) ? $value['op'] : 'contains';

            $get_value = snmp_get($device, $value['oid'], $options, $mib, $mib_dir);
            if (compare_var($get_value, $value['value'], $op) == $check) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Check an array of regexes against a subject if any match, return true
 *
 * @param string $subject the string to match against
 * @param array|string $regexes an array of regexes or single regex to check
 * @return bool if any of the regexes matched, return true
 */
function preg_match_any($subject, $regexes)
{
    foreach ((array)$regexes as $regex) {
        if (preg_match($regex, $subject)) {
            return true;
        }
    }
    return false;
}

/**
 * Perform comparison of two items based on give comparison method
 * Valid comparisons: =, !=, ==, !==, >=, <=, >, <, contains, starts, ends, regex
 * contains, starts, ends: $a haystack, $b needle(s)
 * regex: $a subject, $b regex
 *
 * @param mixed $a
 * @param mixed $b
 * @param string $comparison =, !=, ==, !== >=, <=, >, <, contains, starts, ends, regex
 * @return bool
 */
function compare_var($a, $b, $comparison = '=')
{
    switch ($comparison) {
        case "=":
            return $a == $b;
        case "!=":
            return $a != $b;
        case "==":
            return $a === $b;
        case "!==":
            return $a !== $b;
        case ">=":
            return $a >= $b;
        case "<=":
            return $a <= $b;
        case ">":
            return $a > $b;
        case "<":
            return $a < $b;
        case "contains":
            return str_contains($a, $b);
        case "starts":
            return starts_with($a, $b);
        case "ends":
            return ends_with($a, $b);
        case "regex":
            return (bool)preg_match($b, $a);
        default:
            return false;
    }
}

function percent_colour($perc)
{
    $r = min(255, 5 * ($perc - 25));
    $b = max(0, 255 - (5 * ($perc + 25)));

    return sprintf('#%02x%02x%02x', $r, $b, $b);
}

// Returns the last in/out errors value in RRD
function interface_errors($rrd_file, $period = '-1d')
{
    global $config;
    $errors = array();

    $cmd = $config['rrdtool']." fetch -s $period -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 4,5";
    $data = trim(shell_exec($cmd));
    $in_errors = 0;
    $out_errors = 0;
    foreach (explode("\n", $data) as $entry) {
        list($in, $out) = explode(" ", $entry);
        $in_errors += ($in * 300);
        $out_errors += ($out * 300);
    }
    $errors['in'] = round($in_errors);
    $errors['out'] = round($out_errors);

    return $errors;
}

/**
 * @param $device
 * @return string the logo image path for this device. Images are often wide, not square.
 */
function getLogo($device)
{
    $img = getImageName($device, true, 'images/logos/');
    if (!starts_with($img, 'generic')) {
        return 'images/logos/' . $img;
    }

    return getIcon($device);
}

/**
 * @param array $device
 * @param string $class to apply to the image tag
 * @return string an image tag with the logo for this device. Images are often wide, not square.
 */
function getLogoTag($device, $class = null)
{
    $tag = '<img src="' . getLogo($device) . '" title="' . getImageTitle($device) . '"';
    if (isset($class)) {
        $tag .= " class=\"$class\" ";
    }
    $tag .= ' />';
    return  $tag;
}

/**
 * @param $device
 * @return string the path to the icon image for this device.  Close to square.
 */
function getIcon($device)
{
    return 'images/os/' . getImageName($device);
}

/**
 * @param $device
 * @return string an image tag with the icon for this device.  Close to square.
 */
function getIconTag($device)
{
    return '<img src="' . getIcon($device) . '" title="' . getImageTitle($device) . '"/>';
}

function getImageTitle($device)
{
    return $device['icon'] ? str_replace(array('.svg', '.png'), '', $device['icon']) : $device['os'];
}

function getImageName($device, $use_database = true, $dir = 'images/os/')
{
    global $config;

    $os = strtolower($device['os']);

    // fetch from the database
    if ($use_database && is_file($config['html_dir'] . "/$dir" . $device['icon'])) {
        return $device['icon'];
    }

    // linux specific handling, distro icons
    $distro = null;
    if ($os == "linux") {
        $features = strtolower(trim($device['features']));
        list($distro) = explode(" ", $features);
    }

    $possibilities = array(
        $distro,
        $config['os'][$os]['icon'],
        $os,
    );

    foreach ($possibilities as $basename) {
        foreach (array('.svg', '.png') as $ext) {
            $name = $basename . $ext;
            if (is_file($config['html_dir'] . "/$dir" . $name)) {
                return $name;
            }
        }
    }

    // fallback to the generic icon
    return 'generic.svg';
}

function renamehost($id, $new, $source = 'console')
{
    $host = gethostbyid($id);

    if (!is_dir(get_rrd_dir($new)) && rename(get_rrd_dir($host), get_rrd_dir($new)) === true) {
        dbUpdate(['hostname' => $new, 'ip' => null], 'devices', 'device_id=?', [$id]);
        log_event("Hostname changed -> $new ($source)", $id, 'system', 3);
        return '';
    }

    log_event("Renaming of $host failed", $id, 'system', 5);
    return "Renaming of $host failed\n";
}

function delete_device($id)
{
    global $config, $debug;

    if (isCli() === false) {
        ignore_user_abort(true);
        set_time_limit(0);
    }

    $ret = '';

    $host = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?", array($id));
    if (empty($host)) {
        return "No such host.";
    }

    // Remove IPv4/IPv6 addresses before removing ports as they depend on port_id
    dbQuery("DELETE `ipv4_addresses` FROM `ipv4_addresses` INNER JOIN `ports` ON `ports`.`port_id`=`ipv4_addresses`.`port_id` WHERE `device_id`=?", array($id));
    dbQuery("DELETE `ipv6_addresses` FROM `ipv6_addresses` INNER JOIN `ports` ON `ports`.`port_id`=`ipv6_addresses`.`port_id` WHERE `device_id`=?", array($id));

    foreach (dbFetch("SELECT * FROM `ports` WHERE `device_id` = ?", array($id)) as $int_data) {
        $int_if = $int_data['ifDescr'];
        $int_id = $int_data['port_id'];
        delete_port($int_id);
        $ret .= "Removed interface $int_id ($int_if)\n";
    }

    // Remove sensors manually due to constraints
    foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `device_id` = ?", array($id)) as $sensor) {
        $sensor_id = $sensor['sensor_id'];
        dbDelete('sensors_to_state_indexes', "`sensor_id` = ?", array($sensor_id));
    }
    $fields = array('device_id','host');

    $db_name = dbFetchCell('SELECT DATABASE()');
    foreach ($fields as $field) {
        foreach (dbFetch("SELECT table_name FROM information_schema.columns WHERE table_schema = ? AND column_name = ?", [$db_name, $field]) as $table) {
            $table = $table['table_name'];
            $entries = (int) dbDelete($table, "`$field` =  ?", array($id));
            if ($entries > 0 && $debug === true) {
                $ret .= "$field@$table = #$entries\n";
            }
        }
    }

    $ex = shell_exec("bash -c '( [ ! -d ".trim(get_rrd_dir($host))." ] || rm -vrf ".trim(get_rrd_dir($host))." 2>&1 ) && echo -n OK'");
    $tmp = explode("\n", $ex);
    if ($tmp[sizeof($tmp)-1] != "OK") {
        $ret .= "Could not remove files:\n$ex\n";
    }

    $ret .= "Removed device $host\n";
    log_event("Device $host has been removed", 0, 'system', 3);
    oxidized_reload_nodes();
    return $ret;
}

/**
 * Add a device to LibreNMS
 *
 * @param string $host dns name or ip address
 * @param string $snmp_version If this is empty, try v2c,v3,v1.  Otherwise, use this specific version.
 * @param string $port the port to connect to for snmp
 * @param string $transport udp or tcp
 * @param string $poller_group the poller group this device will belong to
 * @param boolean $force_add add even if the device isn't reachable
 * @param string $port_assoc_mode snmp field to use to determine unique ports
 * @param array $additional an array with additional parameters to take into consideration when adding devices
 *
 * @return int returns the device_id of the added device
 *
 * @throws HostExistsException This hostname already exists
 * @throws HostIpExistsException We already have a host with this IP
 * @throws HostUnreachableException We could not reach this device is some way
 * @throws HostUnreachablePingException We could not ping the device
 * @throws InvalidPortAssocModeException The given port association mode was invalid
 * @throws SnmpVersionUnsupportedException The given snmp version was invalid
 */
function addHost($host, $snmp_version = '', $port = '161', $transport = 'udp', $poller_group = '0', $force_add = false, $port_assoc_mode = 'ifIndex', $additional = array())
{
    global $config;

    // Test Database Exists
    if (host_exists($host)) {
        throw new HostExistsException("Already have host $host");
    }

    // Valid port assoc mode
    if (!in_array($port_assoc_mode, get_port_assoc_modes())) {
        throw new InvalidPortAssocModeException("Invalid port association_mode '$port_assoc_mode'. Valid modes are: " . join(', ', get_port_assoc_modes()));
    }

    // check if we have the host by IP
    if ($config['addhost_alwayscheckip'] === true) {
        $ip = gethostbyname($host);
    } else {
        $ip = $host;
    }
    if ($force_add !== true && ip_exists($ip)) {
        throw new HostIpExistsException("Already have host with this IP $host");
    }

    // Test reachability
    if (!$force_add) {
        $address_family = snmpTransportToAddressFamily($transport);
        $ping_result = isPingable($host, $address_family);
        if (!$ping_result['result']) {
            throw new HostUnreachablePingException("Could not ping $host");
        }
    }

    // if $snmpver isn't set, try each version of snmp
    if (empty($snmp_version)) {
        $snmpvers = Config::get('snmp.version');
    } else {
        $snmpvers = array($snmp_version);
    }

    if (isset($additional['snmp_disable']) && $additional['snmp_disable'] == 1) {
        return createHost($host, '', $snmp_version, $port, $transport, array(), $poller_group, 1, true, $additional);
    }
    $host_unreachable_exception = new HostUnreachableException("Could not connect to $host, please check the snmp details and snmp reachability");
    // try different snmp variables to add the device
    foreach ($snmpvers as $snmpver) {
        if ($snmpver === "v3") {
            // Try each set of parameters from config
            foreach ($config['snmp']['v3'] as $v3) {
                $device = deviceArray($host, null, $snmpver, $port, $transport, $v3, $port_assoc_mode);
                if ($force_add === true || isSNMPable($device)) {
                    return createHost($host, null, $snmpver, $port, $transport, $v3, $poller_group, $port_assoc_mode, $force_add);
                } else {
                    $host_unreachable_exception->addReason("SNMP $snmpver: No reply with credentials " . $v3['authname'] . "/" . $v3['authlevel']);
                }
            }
        } elseif ($snmpver === "v2c" || $snmpver === "v1") {
            // try each community from config
            foreach ($config['snmp']['community'] as $community) {
                $device = deviceArray($host, $community, $snmpver, $port, $transport, null, $port_assoc_mode);

                if ($force_add === true || isSNMPable($device)) {
                    return createHost($host, $community, $snmpver, $port, $transport, array(), $poller_group, $port_assoc_mode, $force_add);
                } else {
                    $host_unreachable_exception->addReason("SNMP $snmpver: No reply with community $community");
                }
            }
        } else {
            throw new SnmpVersionUnsupportedException("Unsupported SNMP Version \"$snmpver\", must be v1, v2c, or v3");
        }
    }
    if (isset($additional['ping_fallback']) && $additional['ping_fallback'] == 1) {
        $additional['snmp_disable'] = 1;
        $additional['os'] = "ping";
        return createHost($host, '', $snmp_version, $port, $transport, array(), $poller_group, 1, true, $additional);
    }
    throw $host_unreachable_exception;
}

function deviceArray($host, $community, $snmpver, $port = 161, $transport = 'udp', $v3 = array(), $port_assoc_mode = 'ifIndex')
{
    $device = array();
    $device['hostname'] = $host;
    $device['port'] = $port;
    $device['transport'] = $transport;

    /* Get port_assoc_mode id if neccessary
     * We can work with names of IDs here */
    if (! is_int($port_assoc_mode)) {
        $port_assoc_mode = get_port_assoc_mode_id($port_assoc_mode);
    }
    $device['port_association_mode'] = $port_assoc_mode;

    $device['snmpver'] = $snmpver;
    if ($snmpver === "v2c" or $snmpver === "v1") {
        $device['community'] = $community;
    } elseif ($snmpver === "v3") {
        $device['authlevel']  = $v3['authlevel'];
        $device['authname']   = $v3['authname'];
        $device['authpass']   = $v3['authpass'];
        $device['authalgo']   = $v3['authalgo'];
        $device['cryptopass'] = $v3['cryptopass'];
        $device['cryptoalgo'] = $v3['cryptoalgo'];
    }

    return $device;
}

function formatUptime($diff, $format = "long")
{
    $yearsDiff = floor($diff/31536000);
    $diff -= $yearsDiff*31536000;
    $daysDiff = floor($diff/86400);
    $diff -= $daysDiff*86400;
    $hrsDiff = floor($diff/60/60);
    $diff -= $hrsDiff*60*60;
    $minsDiff = floor($diff/60);
    $diff -= $minsDiff*60;
    $secsDiff = $diff;

    $uptime = "";

    if ($format == "short") {
        if ($yearsDiff > '0') {
            $uptime .= $yearsDiff . "y ";
        }
        if ($daysDiff > '0') {
            $uptime .= $daysDiff . "d ";
        }
        if ($hrsDiff > '0') {
            $uptime .= $hrsDiff . "h ";
        }
        if ($minsDiff > '0') {
            $uptime .= $minsDiff . "m ";
        }
        if ($secsDiff > '0') {
            $uptime .= $secsDiff . "s ";
        }
    } else {
        if ($yearsDiff > '0') {
            $uptime .= $yearsDiff . " years, ";
        }
        if ($daysDiff > '0') {
            $uptime .= $daysDiff . " day" . ($daysDiff != 1 ? 's' : '') . ", ";
        }
        if ($hrsDiff > '0') {
            $uptime .= $hrsDiff     . "h ";
        }
        if ($minsDiff > '0') {
            $uptime .= $minsDiff   . "m ";
        }
        if ($secsDiff > '0') {
            $uptime .= $secsDiff   . "s ";
        }
    }
    return trim($uptime);
}

function isSNMPable($device)
{
    global $config;

    $pos = snmp_check($device);
    if ($pos === true) {
        return true;
    } else {
        $pos = snmp_get($device, "sysObjectID.0", "-Oqv", "SNMPv2-MIB");
        if ($pos === '' || $pos === false) {
            return false;
        } else {
            return true;
        }
    }
}

/**
 * Check if the given host responds to ICMP echo requests ("pings").
 *
 * @param string $hostname The hostname or IP address to send ping requests to.
 * @param string $address_family The address family ('ipv4' or 'ipv6') to use. Defaults to IPv4.
 * Will *not* be autodetected for IP addresses, so it has to be set to 'ipv6' when pinging an IPv6 address or an IPv6-only host.
 * @param array $attribs The device attributes
 *
 * @return array  'result' => bool pingable, 'last_ping_timetaken' => int time for last ping, 'db' => fping results
 */
function isPingable($hostname, $address_family = 'ipv4', $attribs = [])
{
    if (can_ping_device($attribs) !== true) {
        return [
            'result' => true,
            'last_ping_timetaken' => 0
        ];
    }

    $status = fping(
        $hostname,
        Config::get('fping_options.count', 3),
        Config::get('fping_options.interval', 500),
        Config::get('fping_options.timeout', 500),
        $address_family
    );

    return [
        'result' => ($status['exitcode'] == 0 && $status['loss'] < 100),
        'last_ping_timetaken' => $status['avg'],
        'db' => array_intersect_key($status, array_flip(['xmt','rcv','loss','min','max','avg']))
    ];
}

function getpollergroup($poller_group = '0')
{
    //Is poller group an integer
    if (is_int($poller_group) || ctype_digit($poller_group)) {
        return $poller_group;
    } else {
        //Check if it contains a comma
        if (strpos($poller_group, ',')!== false) {
            //If it has a comma use the first element as the poller group
            $poller_group_array=explode(',', $poller_group);
            return getpollergroup($poller_group_array[0]);
        } else {
            if ($config['distributed_poller_group']) {
                //If not use the poller's group from the config
                return getpollergroup($config['distributed_poller_group']);
            } else {
                //If all else fails use default
                return '0';
            }
        }
    }
}

/**
 * Add a host to the database
 *
 * @param string $host The IP or hostname to add
 * @param string $community The snmp community
 * @param string $snmpver snmp version: v1 | v2c | v3
 * @param int $port SNMP port number
 * @param string $transport SNMP transport: udp | udp6 | udp | tcp6
 * @param array $v3 SNMPv3 settings required array keys: authlevel, authname, authpass, authalgo, cryptopass, cryptoalgo
 * @param int $poller_group distributed poller group to assign this host to
 * @param string $port_assoc_mode field to use to identify ports: ifIndex, ifName, ifDescr, ifAlias
 * @param bool $force_add Do not detect the host os
 * @param array $additional an array with additional parameters to take into consideration when adding devices
 * @return int the id of the added host
 * @throws HostExistsException Throws this exception if the host already exists
 * @throws Exception Throws this exception if insertion into the database fails
 */
function createHost(
    $host,
    $community,
    $snmpver,
    $port = 161,
    $transport = 'udp',
    $v3 = array(),
    $poller_group = 0,
    $port_assoc_mode = 'ifIndex',
    $force_add = false,
    $additional = array()
) {
    $host = trim(strtolower($host));

    $poller_group=getpollergroup($poller_group);

    /* Get port_assoc_mode id if necessary
     * We can work with names of IDs here */
    if (! is_int($port_assoc_mode)) {
        $port_assoc_mode = get_port_assoc_mode_id($port_assoc_mode);
    }

    $device = array(
        'hostname' => $host,
        'sysName' => $additional['sysName'] ? $additional['sysName'] : $host,
        'os' => $additional['os'] ? $additional['os'] : 'generic',
        'hardware' => $additional['hardware'] ? $additional['hardware'] : null,
        'community' => $community,
        'port' => $port,
        'transport' => $transport,
        'status' => '1',
        'snmpver' => $snmpver,
        'poller_group' => $poller_group,
        'status_reason' => '',
        'port_association_mode' => $port_assoc_mode,
        'snmp_disable' => $additional['snmp_disable'] ? $additional['snmp_disable'] : 0,
    );

    $device = array_merge($device, $v3);  // merge v3 settings

    if ($force_add !== true) {
        $device['os'] = getHostOS($device);

        $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
        if (host_exists($host, $snmphost)) {
            throw new HostExistsException("Already have host $host ($snmphost) due to duplicate sysName");
        }
    }

    $device_id = dbInsert($device, 'devices');
    if ($device_id) {
        return $device_id;
    }

    throw new \Exception("Failed to add host to the database, please run ./validate.php");
}

function isDomainResolves($domain)
{
    if (gethostbyname($domain) != $domain) {
        return true;
    }

    $records = dns_get_record($domain);  // returns array or false
    return !empty($records);
}

function hoststatus($id)
{
    return dbFetchCell("SELECT `status` FROM `devices` WHERE `device_id` = ?", array($id));
}

function match_network($nets, $ip, $first = false)
{
    $return = false;
    if (!is_array($nets)) {
        $nets = array ($nets);
    }
    foreach ($nets as $net) {
        $rev = (preg_match("/^\!/", $net)) ? true : false;
        $net = preg_replace("/^\!/", "", $net);
        $ip_arr  = explode('/', $net);
        $net_long = ip2long($ip_arr[0]);
        $x        = ip2long($ip_arr[1]);
        $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
        $ip_long  = ip2long($ip);
        if ($rev) {
            if (($ip_long & $mask) == ($net_long & $mask)) {
                return false;
            }
        } else {
            if (($ip_long & $mask) == ($net_long & $mask)) {
                $return = true;
            }
            if ($first && $return) {
                return true;
            }
        }
    }

    return $return;
}

// FIXME port to LibreNMS\Util\IPv6 class
function snmp2ipv6($ipv6_snmp)
{
    # Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
    # < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
    $ipv6 = array_slice(explode('.', $ipv6_snmp), -16);
    $ipv6_2 = array();

    for ($i = 0; $i <= 15; $i++) {
        $ipv6[$i] = zeropad(dechex($ipv6[$i]));
    }
    for ($i = 0; $i <= 15; $i+=2) {
        $ipv6_2[] = $ipv6[$i] . $ipv6[$i+1];
    }

    return implode(':', $ipv6_2);
}

function get_astext($asn)
{
    global $cache;

    if (Config::has("astext.$asn")) {
        return Config::get("astext.$asn");
    }

    if (isset($cache['astext'][$asn])) {
        return $cache['astext'][$asn];
    }

    $result = @dns_get_record("AS$asn.asn.cymru.com", DNS_TXT);
    if (!empty($result[0]['txt'])) {
        $txt = explode('|', $result[0]['txt']);
        $result = trim($txt[4], ' "');
        $cache['astext'][$asn] = $result;
        return $result;
    }

    return '';
}

/**
 * Log events to the event table
 *
 * @param string $text message describing the event
 * @param array|int $device device array or device_id
 * @param string $type brief category for this event. Examples: sensor, state, stp, system, temperature, interface
 * @param int $severity 1: ok, 2: info, 3: notice, 4: warning, 5: critical, 0: unknown
 * @param int $reference the id of the referenced entity.  Supported types: interface
 */
function log_event($text, $device = null, $type = null, $severity = 2, $reference = null)
{
    if (!is_array($device)) {
        $device = device_by_id_cache($device);
    }

    $insert = array('host' => ($device['device_id'] ?: 0),
        'device_id' => ($device['device_id'] ?: 0),
        'reference' => ($reference ?: "NULL"),
        'type' => ($type ?: "NULL"),
        'datetime' => array("NOW()"),
        'severity' => $severity,
        'message' => $text,
        'username'  => isset(LegacyAuth::user()->username) ? LegacyAuth::user()->username : '',
     );

    dbInsert($insert, 'eventlog');
}

// Parse string with emails. Return array with email (as key) and name (as value)
function parse_email($emails)
{
    $result = array();
    $regex = '/^[\"\']?([^\"\']+)[\"\']?\s{0,}<([^@]+@[^>]+)>$/';
    if (is_string($emails)) {
        $emails = preg_split('/[,;]\s{0,}/', $emails);
        foreach ($emails as $email) {
            if (preg_match($regex, $email, $out, PREG_OFFSET_CAPTURE)) {
                $result[$out[2][0]] = $out[1][0];
            } else {
                if (strpos($email, "@")) {
                    $from_name = Config::get('email_user');
                    $result[$email] = $from_name;
                }
            }
        }
    } else {
        // Return FALSE if input not string
        return false;
    }
    return $result;
}

function send_mail($emails, $subject, $message, $html = false)
{
    global $config;
    if (is_array($emails) || ($emails = parse_email($emails))) {
        d_echo("Attempting to email $subject to: " . implode('; ', array_keys($emails)) . PHP_EOL);
        $mail = new PHPMailer(true);
        try {
            $mail->Hostname = php_uname('n');

            foreach (parse_email($config['email_from']) as $from => $from_name) {
                $mail->setFrom($from, $from_name);
            }
            foreach ($emails as $email => $email_name) {
                $mail->addAddress($email, $email_name);
            }
            $mail->Subject = $subject;
            $mail->XMailer = $config['project_name_version'];
            $mail->CharSet = 'utf-8';
            $mail->WordWrap = 76;
            $mail->Body = $message;
            if ($html) {
                $mail->isHTML(true);
            }
            switch (strtolower(trim($config['email_backend']))) {
                case 'sendmail':
                    $mail->Mailer = 'sendmail';
                    $mail->Sendmail = $config['email_sendmail_path'];
                    break;
                case 'smtp':
                    $mail->isSMTP();
                    $mail->Host       = $config['email_smtp_host'];
                    $mail->Timeout    = $config['email_smtp_timeout'];
                    $mail->SMTPAuth   = $config['email_smtp_auth'];
                    $mail->SMTPSecure = $config['email_smtp_secure'];
                    $mail->Port       = $config['email_smtp_port'];
                    $mail->Username   = $config['email_smtp_username'];
                    $mail->Password   = $config['email_smtp_password'];
                    $mail->SMTPAutoTLS= $config['email_auto_tls'];
                    $mail->SMTPDebug  = false;
                    break;
                default:
                    $mail->Mailer = 'mail';
                    break;
            }
            $mail->send();
            return true;
        } catch (phpmailerException $e) {
            return $e->errorMessage();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    return "No contacts found";
}

function formatCiscoHardware(&$device, $short = false)
{
    if ($device['os'] == "ios") {
        if ($device['hardware']) {
            if (preg_match("/^WS-C([A-Za-z0-9]+)/", $device['hardware'], $matches)) {
                if (!$short) {
                    $device['hardware'] = "Catalyst " . $matches[1] . " (" . $device['hardware'] . ")";
                } else {
                    $device['hardware'] = "Catalyst " . $matches[1];
                }
            } elseif (preg_match("/^CISCO([0-9]+)(.*)/", $device['hardware'], $matches)) {
                if (!$short && $matches[2]) {
                    $device['hardware'] = "Cisco " . $matches[1] . " (" . $device['hardware'] . ")";
                } else {
                    $device['hardware'] = "Cisco " . $matches[1];
                }
            }
        } else {
            if (preg_match("/Cisco IOS Software, C([A-Za-z0-9]+) Software.*/", $device['sysDescr'], $matches)) {
                $device['hardware'] = "Catalyst " . $matches[1];
            } elseif (preg_match("/Cisco IOS Software, ([0-9]+) Software.*/", $device['sysDescr'], $matches)) {
                $device['hardware'] = "Cisco " . $matches[1];
            }
        }
    }
}

function hex2str($hex)
{
    $string='';

    for ($i = 0; $i < strlen($hex)-1; $i+=2) {
        $string .= chr(hexdec(substr($hex, $i, 2)));
    }

    return $string;
}

# Convert an SNMP hex string to regular string
function snmp_hexstring($hex)
{
    return hex2str(str_replace(' ', '', str_replace(' 00', '', $hex)));
}

# Check if the supplied string is an SNMP hex string
function isHexString($str)
{
    return (bool)preg_match("/^[a-f0-9][a-f0-9]( [a-f0-9][a-f0-9])*$/is", trim($str));
}

# Include all .inc.php files in $dir
function include_dir($dir, $regex = "")
{
    global $device, $config, $valid;

    if ($regex == "") {
        $regex = "/\.inc\.php$/";
    }

    if ($handle = opendir($config['install_dir'] . '/' . $dir)) {
        while (false !== ($file = readdir($handle))) {
            if (filetype($config['install_dir'] . '/' . $dir . '/' . $file) == 'file' && preg_match($regex, $file)) {
                d_echo("Including: " . $config['install_dir'] . '/' . $dir . '/' . $file . "\n");

                include($config['install_dir'] . '/' . $dir . '/' . $file);
            }
        }

        closedir($handle);
    }
}

/**
 * Check if port is valid to poll.
 * Settings: empty_ifdescr, good_if, bad_if, bad_if_regexp, bad_ifname_regexp, bad_ifalias_regexp, bad_iftype
 *
 * @param array $port
 * @param array $device
 * @return bool
 */
function is_port_valid($port, $device)
{
    // check empty values first
    if (empty($port['ifDescr'])) {
        // If these are all empty, we are just going to show blank names in the ui
        if (empty($port['ifAlias']) && empty($port['ifName'])) {
            d_echo("ignored: empty ifDescr, ifAlias and ifName\n");
            return false;
        }

        // ifDescr should not be empty unless it is explicitly allowed
        if (!Config::getOsSetting($device['os'], 'empty_ifdescr', false)) {
            d_echo("ignored: empty ifDescr\n");
            return false;
        }
    }

    $ifDescr = $port['ifDescr'];
    $ifName  = $port['ifName'];
    $ifAlias = $port['ifAlias'];
    $ifType  = $port['ifType'];

    if (str_i_contains($ifDescr, Config::getOsSetting($device['os'], 'good_if'))) {
        return true;
    }

    foreach (Config::getCombined($device['os'], 'bad_if') as $bi) {
        if (str_i_contains($ifDescr, $bi)) {
            d_echo("ignored by ifDescr: $ifDescr (matched: $bi)\n");
            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_if_regexp') as $bir) {
        if (preg_match($bir ."i", $ifDescr)) {
            d_echo("ignored by ifDescr: $ifDescr (matched: $bir)\n");
            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifname_regexp') as $bnr) {
        if (preg_match($bnr ."i", $ifName)) {
            d_echo("ignored by ifName: $ifName (matched: $bnr)\n");
            return false;
        }
    }


    foreach (Config::getCombined($device['os'], 'bad_ifalias_regexp') as $bar) {
        if (preg_match($bar ."i", $ifAlias)) {
            d_echo("ignored by ifName: $ifAlias (matched: $bar)\n");
            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_iftype') as $bt) {
        if (str_contains($ifType, $bt)) {
            d_echo("ignored by ifType: $ifType (matched: $bt )\n");
            return false;
        }
    }

    return true;
}

function scan_new_plugins()
{

    global $config;

    $installed = 0; // Track how many plugins we install.

    if (file_exists($config['plugin_dir'])) {
        $plugin_files = scandir($config['plugin_dir']);
        foreach ($plugin_files as $name) {
            if (is_dir($config['plugin_dir'].'/'.$name)) {
                if ($name != '.' && $name != '..') {
                    if (is_file($config['plugin_dir'].'/'.$name.'/'.$name.'.php') && is_file($config['plugin_dir'].'/'.$name.'/'.$name.'.inc.php')) {
                        $plugin_id = dbFetchRow("SELECT `plugin_id` FROM `plugins` WHERE `plugin_name` = '$name'");
                        if (empty($plugin_id)) {
                            if (dbInsert(array('plugin_name' => $name, 'plugin_active' => '0'), 'plugins')) {
                                $installed++;
                            }
                        }
                    }
                }
            }
        }
    }

    return( $installed );
}

function validate_device_id($id)
{

    global $config;
    if (empty($id) || !is_numeric($id)) {
        $return = false;
    } else {
        $device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `device_id` = ?", array($id));
        if ($device_id == $id) {
            $return = true;
        } else {
            $return = false;
        }
    }
    return($return);
}

// The original source of this code is from Stackoverflow (www.stackoverflow.com).
// http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
// Answer provided by stewe (http://stackoverflow.com/users/3202187/ulk200
if (!defined('JSON_UNESCAPED_SLASHES')) {
    define('JSON_UNESCAPED_SLASHES', 64);
}
if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 128);
}
if (!defined('JSON_UNESCAPED_UNICODE')) {
    define('JSON_UNESCAPED_UNICODE', 256);
}

function _json_encode($data, $options = 448)
{
    if (version_compare(PHP_VERSION, '5.4', '>=')) {
        return json_encode($data, $options);
    } else {
        return _json_format(json_encode($data), $options);
    }
}

function _json_format($json, $options = 448)
{
    $prettyPrint = (bool) ($options & JSON_PRETTY_PRINT);
    $unescapeUnicode = (bool) ($options & JSON_UNESCAPED_UNICODE);
    $unescapeSlashes = (bool) ($options & JSON_UNESCAPED_SLASHES);

    if (!$prettyPrint && !$unescapeUnicode && !$unescapeSlashes) {
        return $json;
    }

    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $outOfQuotes = true;
    $buffer = '';
    $noescape = true;

    for ($i = 0; $i < $strLen; $i++) {
        // Grab the next character in the string
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ('"' === $char && $noescape) {
            $outOfQuotes = !$outOfQuotes;
        }

        if (!$outOfQuotes) {
            $buffer .= $char;
            $noescape = '\\' === $char ? !$noescape : true;
            continue;
        } elseif ('' !== $buffer) {
            if ($unescapeSlashes) {
                $buffer = str_replace('\\/', '/', $buffer);
            }

            if ($unescapeUnicode && function_exists('mb_convert_encoding')) {
                // http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
                $buffer = preg_replace_callback(
                    '/\\\\u([0-9a-f]{4})/i',
                    function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    },
                    $buffer
                );
            }

            $result .= $buffer . $char;
            $buffer = '';
            continue;
        } elseif (false !== strpos(" \t\r\n", $char)) {
            continue;
        }

        if (':' === $char) {
            // Add a space after the : character
            $char .= ' ';
        } elseif (('}' === $char || ']' === $char)) {
            $pos--;
            $prevChar = substr($json, $i - 1, 1);

            if ('{' !== $prevChar && '[' !== $prevChar) {
                // If this character is the end of an element,
                // output a new line and indent the next line
                $result .= $newLine;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            } else {
                // Collapse empty {} and []
                $result = rtrim($result) . "\n\n" . $indentStr;
            }
        }

        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line
        if (',' === $char || '{' === $char || '[' === $char) {
            $result .= $newLine;

            if ('{' === $char || '[' === $char) {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
    }
    // If buffer not empty after formating we have an unclosed quote
    if (strlen($buffer) > 0) {
        //json is incorrectly formatted
        $result = false;
    }

    return $result;
}

function convert_delay($delay)
{
    $delay = preg_replace('/\s/', '', $delay);
    if (strstr($delay, 'm', true)) {
        $delay_sec = $delay * 60;
    } elseif (strstr($delay, 'h', true)) {
        $delay_sec = $delay * 3600;
    } elseif (strstr($delay, 'd', true)) {
        $delay_sec = $delay * 86400;
    } elseif (is_numeric($delay)) {
        $delay_sec = $delay;
    } else {
        $delay_sec = 300;
    }
    return($delay_sec);
}

function guidv4($data)
{
    // http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid#15875555
    // From: Jack http://stackoverflow.com/users/1338292/ja%CD%A2ck
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * @param $curl
 */
function set_curl_proxy($curl)
{
    $proxy = get_proxy();

    $tmp = rtrim($proxy, "/");
    $proxy = str_replace(array("http://", "https://"), "", $tmp);
    if (!empty($proxy)) {
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
    }
}

/**
 * Return the proxy url
 *
 * @return array|bool|false|string
 */
function get_proxy()
{
    global $config;

    if (getenv('http_proxy')) {
        return getenv('http_proxy');
    } elseif (getenv('https_proxy')) {
        return getenv('https_proxy');
    } elseif (isset($config['callback_proxy'])) {
        return $config['callback_proxy'];
    } elseif (isset($config['http_proxy'])) {
        return $config['http_proxy'];
    }
    return false;
}

function target_to_id($target)
{
    if ($target[0].$target[1] == "g:") {
        $target = "g".dbFetchCell('SELECT id FROM device_groups WHERE name = ?', array(substr($target, 2)));
    } else {
        $target = dbFetchCell('SELECT device_id FROM devices WHERE hostname = ?', array($target));
    }
    return $target;
}

function id_to_target($id)
{
    if ($id[0] == "g") {
        $id = 'g:'.dbFetchCell("SELECT name FROM device_groups WHERE id = ?", array(substr($id, 1)));
    } else {
        $id = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?", array($id));
    }
    return $id;
}

function first_oid_match($device, $list)
{
    foreach ($list as $item) {
        $tmp = trim(snmp_get($device, $item, "-Ovq"), '" ');
        if (!empty($tmp)) {
            return $tmp;
        }
    }
}


function fix_integer_value($value)
{
    if ($value < 0) {
        $return = 4294967296+$value;
    } else {
        $return = $value;
    }
    return $return;
}

function ip_exists($ip)
{
    // Function to check if an IP exists in the DB already
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
        $dbresult = dbFetchRow("SELECT `ipv6_address_id` FROM `ipv6_addresses` WHERE `ipv6_address` = ? OR `ipv6_compressed` = ?", array($ip, $ip));
        return !empty($dbresult);
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
        $dbresult = dbFetchRow("SELECT `ipv4_address_id` FROM `ipv4_addresses` WHERE `ipv4_address` = ?", array($ip));
        return !empty($dbresult);
    }

    // not an ipv4 or ipv6 address...
    return false;
}

/**
 * Run fping against a hostname/ip in count mode and collect stats.
 *
 * @param string $host
 * @param int $count (min 1)
 * @param int $interval (min 20)
 * @param int $timeout (not more than $interval)
 * @param string $address_family ipv4 or ipv6
 * @return array
 */
function fping($host, $count = 3, $interval = 1000, $timeout = 500, $address_family = 'ipv4')
{
    // Default to ipv4
    $fping_name = $address_family == 'ipv6' ? 'fping6' : 'fping';
    $fping_path = Config::get($fping_name, $fping_name);

    // build the parameters
    $params = '-e -q -c ' . max($count, 1);

    $interval = max($interval, 20);
    $params .= ' -p ' . $interval;

    $params .= ' -t ' . max($timeout, $interval);

    $cmd = "$fping_path $params $host";

    d_echo("[FPING] $cmd\n");

    $process = new Process($cmd);
    $process->run();
    $output = $process->getErrorOutput();

    preg_match('#= (\d+)/(\d+)/(\d+)%, min/avg/max = ([\d.]+)/([\d.]+)/([\d.]+)$#', $output, $parsed);
    list(, $xmt, $rcv, $loss, $min, $avg, $max) = $parsed;

    if ($loss < 0) {
        $xmt = 1;
        $rcv = 1;
        $loss = 100;
    }

    $response = [
        'xmt'  => set_numeric($xmt),
        'rcv'  => set_numeric($rcv),
        'loss' => set_numeric($loss),
        'min'  => set_numeric($min),
        'max'  => set_numeric($max),
        'avg'  => set_numeric($avg),
        'exitcode' => $process->getExitCode(),
    ];
    d_echo($response);

    return $response;
}

function function_check($function)
{
    return function_exists($function);
}

function force_influx_data($data)
{
   /*
    * It is not trivial to detect if something is a float or an integer, and
    * therefore may cause breakages on inserts.
    * Just setting every number to a float gets around this, but may introduce
    * inefficiencies.
    * I've left the detection statement in there for a possible change in future,
    * but currently everything just gets set to a float.
    */

    if (is_numeric($data)) {
        // If it is an Integer
        if (ctype_digit($data)) {
            return floatval($data);
        // Else it is a float
        } else {
            return floatval($data);
        }
    } else {
        return $data;
    }
}// end force_influx_data

/**
 * Try to determine the address family (IPv4 or IPv6) associated with an SNMP
 * transport specifier (like "udp", "udp6", etc.).
 *
 * @param string $transport The SNMP transport specifier, for example "udp",
 *                          "udp6", "tcp", or "tcp6". See `man snmpcmd`,
 *                          section "Agent Specification" for a full list.
 *
 * @return string The address family associated with the given transport
 *             specifier: 'ipv4' (or local connections not associated
 *             with an IP stack) or 'ipv6'.
 */
function snmpTransportToAddressFamily($transport)
{
    $ipv6_snmp_transport_specifiers = ['udp6', 'udpv6', 'udpipv6', 'tcp6', 'tcpv6', 'tcpipv6'];

    if (in_array($transport, $ipv6_snmp_transport_specifiers)) {
        return 'ipv6';
    }

    return 'ipv4';
}

/**
 * Checks if the $hostname provided exists in the DB already
 *
 * @param string $hostname The hostname to check for
 * @param string $sysName The sysName to check
 * @return bool true if hostname already exists
 *              false if hostname doesn't exist
 */
function host_exists($hostname, $sysName = null)
{
    global $config;

    $query = "SELECT COUNT(*) FROM `devices` WHERE `hostname`=?";
    $params = array($hostname);

    if (!empty($sysName) && !$config['allow_duplicate_sysName']) {
        $query .= " OR `sysName`=?";
        $params[] = $sysName;

        if (!empty($config['mydomain'])) {
            $full_sysname = rtrim($sysName, '.') . '.' . $config['mydomain'];
            $query .= " OR `sysName`=?";
            $params[] = $full_sysname;
        }
    }
    return dbFetchCell($query, $params) > 0;
}

function oxidized_reload_nodes()
{

    global $config;

    if ($config['oxidized']['enabled'] === true && $config['oxidized']['reload_nodes'] === true && isset($config['oxidized']['url'])) {
        $oxidized_reload_url = $config['oxidized']['url'] . '/reload.json';
        $ch = curl_init($oxidized_reload_url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}

/**
 * Perform DNS lookup
 *
 * @param array $device Device array from database
 * @param string $type The type of record to lookup
 *
 * @return string ip
 *
**/
function dnslookup($device, $type = false, $return = false)
{
    if (filter_var($device['hostname'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == true || filter_var($device['hostname'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == true) {
        return false;
    }
    if (empty($type)) {
        // We are going to use the transport to work out the record type
        if ($device['transport'] == 'udp6' || $device['transport'] == 'tcp6') {
            $type = DNS_AAAA;
            $return = 'ipv6';
        } else {
            $type = DNS_A;
            $return = 'ip';
        }
    }
    if (empty($return)) {
        return false;
    }
    $record = dns_get_record($device['hostname'], $type);
    return $record[0][$return];
}//end dnslookup




/**
 * Run rrdtool info on a file path
 *
 * @param string $path Path to pass to rrdtool info
 * @param string $stdOutput Variable to recieve the output of STDOUT
 * @param string $stdError Variable to recieve the output of STDERR
 *
 * @return int exit code
 *
**/

function rrdtest($path, &$stdOutput, &$stdError)
{
    global $config;
    //rrdtool info <escaped rrd path>
    $command = $config['rrdtool'].' info '.escapeshellarg($path);
    $process = proc_open(
        $command,
        array (
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        ),
        $pipes
    );

    if (!is_resource($process)) {
        throw new \RuntimeException('Could not create a valid process');
    }

    $status = proc_get_status($process);
    while ($status['running']) {
        usleep(2000); // Sleep 2000 microseconds or 2 milliseconds
        $status = proc_get_status($process);
    }

    $stdOutput = stream_get_contents($pipes[1]);
    $stdError  = stream_get_contents($pipes[2]);
    proc_close($process);
    return $status['exitcode'];
}

/**
 * Create a new state index.  Update translations if $states is given.
 *
 * For for backward compatibility:
 *   Returns null if $states is empty, $state_name already exists, and contains state translations
 *
 * @param string $state_name the unique name for this state translation
 * @param array $states array of states, each must contain keys: descr, graph, value, generic
 * @return int|null
 */
function create_state_index($state_name, $states = array())
{
    $state_index_id = dbFetchCell('SELECT `state_index_id` FROM state_indexes WHERE state_name = ? LIMIT 1', array($state_name));
    if (!is_numeric($state_index_id)) {
        $state_index_id = dbInsert(array('state_name' => $state_name), 'state_indexes');

        // legacy code, return index so states are created
        if (empty($states)) {
            return $state_index_id;
        }
    }

    // check or synchronize states
    if (empty($states)) {
        $translations = dbFetchRows('SELECT * FROM `state_translations` WHERE `state_index_id` = ?', array($state_index_id));
        if (count($translations) == 0) {
            // If we don't have any translations something has gone wrong so return the state_index_id so they get created.
            return $state_index_id;
        }
    } else {
        sync_sensor_states($state_index_id, $states);
    }

    return null;
}

/**
 * Synchronize the sensor state translations with the database
 *
 * @param int $state_index_id index of the state
 * @param array $states array of states, each must contain keys: descr, graph, value, generic
 */
function sync_sensor_states($state_index_id, $states)
{
    $new_translations = array_reduce($states, function ($array, $state) use ($state_index_id) {
        $array[$state['value']] = array(
            'state_index_id' => $state_index_id,
            'state_descr' => $state['descr'],
            'state_draw_graph' => $state['graph'],
            'state_value' => $state['value'],
            'state_generic_value' => $state['generic']
        );
        return $array;
    }, array());

    $existing_translations = dbFetchRows(
        'SELECT `state_index_id`,`state_descr`,`state_draw_graph`,`state_value`,`state_generic_value` FROM `state_translations` WHERE `state_index_id`=?',
        array($state_index_id)
    );

    foreach ($existing_translations as $translation) {
        $value = $translation['state_value'];
        if (isset($new_translations[$value])) {
            if ($new_translations[$value] != $translation) {
                dbUpdate(
                    $new_translations[$value],
                    'state_translations',
                    '`state_index_id`=? AND `state_value`=?',
                    array($state_index_id, $value)
                );
            }

            // this translation is synchronized, it doesn't need to be inserted
            unset($new_translations[$value]);
        } else {
            dbDelete('state_translations', '`state_index_id`=? AND `state_value`=?', array($state_index_id, $value));
        }
    }

    // insert any new translations
    dbBulkInsert($new_translations, 'state_translations');
}

function create_sensor_to_state_index($device, $state_name, $index)
{
    $sensor_entry = dbFetchRow('SELECT sensor_id FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?', array(
        'state',
        $device['device_id'],
        $state_name,
        $index
    ));
    $state_indexes_entry = dbFetchRow('SELECT state_index_id FROM `state_indexes` WHERE `state_name` = ?', array(
        $state_name
    ));
    if (!empty($sensor_entry['sensor_id']) && !empty($state_indexes_entry['state_index_id'])) {
        $insert = array(
            'sensor_id' => $sensor_entry['sensor_id'],
            'state_index_id' => $state_indexes_entry['state_index_id'],
        );
        foreach ($insert as $key => $val_check) {
            if (!isset($val_check)) {
                unset($insert[$key]);
            }
        }

        dbInsert($insert, 'sensors_to_state_indexes');
    }
}

function delta_to_bits($delta, $period)
{
    return round(($delta * 8 / $period), 2);
}

function report_this($message)
{
    global $config;
    return '<h2>'.$message.' Please <a href="'.$config['project_issues'].'">report this</a> to the '.$config['project_name'].' developers.</h2>';
}//end report_this()

function hytera_h2f($number, $nd)
{
    if (strlen(str_replace(" ", "", $number)) == 4) {
        $hex = '';
        for ($i = 0; $i < strlen($number); $i++) {
            $byte = strtoupper(dechex(ord($number{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)).$byte;
            $hex.=$byte." ";
        }
        $number = $hex;
        unset($hex);
    }
    $r = '';
    $y = explode(' ', $number);
    foreach ($y as $z) {
        $r = $z . '' . $r;
    }

    $hex = array();
    $number = substr($r, 0, -1);
    //$number = str_replace(" ", "", $number);
    for ($i=0; $i<strlen($number); $i++) {
        $hex[]=substr($number, $i, 1);
    }

    $dec = array();
    $hexCount = count($hex);
    for ($i=0; $i<$hexCount; $i++) {
        $dec[]=hexdec($hex[$i]);
    }

    $binfinal = "";
    $decCount = count($dec);
    for ($i=0; $i<$decCount; $i++) {
        $binfinal.=sprintf("%04d", decbin($dec[$i]));
    }

    $sign=substr($binfinal, 0, 1);
    $exp=substr($binfinal, 1, 8);
    $exp=bindec($exp);
    $exp-=127;
    $scibin=substr($binfinal, 9);
    $binint=substr($scibin, 0, $exp);
    $binpoint=substr($scibin, $exp);
    $intnumber=bindec("1".$binint);

    $tmppoint = "";
    for ($i=0; $i<strlen($binpoint); $i++) {
        $tmppoint[]=substr($binpoint, $i, 1);
    }

    $tmppoint=array_reverse($tmppoint);
    $tpointnumber=number_format($tmppoint[0]/2, strlen($binpoint), '.', '');

    $pointnumber = "";
    for ($i=1; $i<strlen($binpoint); $i++) {
        $pointnumber=number_format($tpointnumber/2, strlen($binpoint), '.', '');
        $tpointnumber=$tmppoint[$i+1].substr($pointnumber, 1);
    }

    $floatfinal=$intnumber+$pointnumber;

    if ($sign==1) {
        $floatfinal=-$floatfinal;
    }

    return number_format($floatfinal, $nd, '.', '');
}

/*
 * Cisco CIMC functions
 */
// Create an entry in the entPhysical table if it doesnt already exist.
function setCIMCentPhysical($location, $data, &$entphysical, &$index)
{
    // Go get the location, this will create it if it doesnt exist.
    $entPhysicalIndex = getCIMCentPhysical($location, $entphysical, $index);

    // See if we need to update
    $update = array();
    foreach ($data as $key => $value) {
        // Is the Array(DB) value different to the supplied data
        if ($entphysical[$location][$key] != $value) {
            $update[$key] = $value;
            $entphysical[$location][$key] = $value;
        } // End if
    } // end foreach

    // Do we need to update
    if (count($update) > 0) {
        dbUpdate($update, 'entPhysical', '`entPhysical_id` = ?', array($entphysical[$location]['entPhysical_id']));
    }
    $entPhysicalId = $entphysical[$location]['entPhysical_id'];
    return array($entPhysicalId, $entPhysicalIndex);
}

function getCIMCentPhysical($location, &$entphysical, &$index)
{
    global $device;

    // Level 1 - Does the location exist
    if (isset($entphysical[$location])) {
        // Yes, return the entPhysicalIndex.
        return $entphysical[$location]['entPhysicalIndex'];
    } else {
        /*
         * No, the entry doesnt exist.
         * Find its parent so we can create it.
         */

        // Pull apart the location
        $parts = explode('/', $location);

        // Level 2 - Are we at the root
        if (count($parts) == 1) {
            // Level 2 - Yes. We are the root, there is no parent
            d_echo("ROOT - ".$location."\n");
            $shortlocation = $location;
            $parent = 0;
        } else {
            // Level 2 - No. Need to go deeper.
            d_echo("NON-ROOT - ".$location."\n");
            $shortlocation = array_pop($parts);
            $parentlocation = implode('/', $parts);
            d_echo("Decend - parent location: ".$parentlocation."\n");
            $parent = getCIMCentPhysical($parentlocation, $entphysical, $index);
        } // end if - Level 2
        d_echo("Parent: ".$parent."\n");

        // Now we have an ID, create the entry.
        $index++;
        $insert = array(
            'device_id'                 => $device['device_id'],
            'entPhysicalIndex'          => $index,
            'entPhysicalClass'          => 'container',
            'entPhysicalVendorType'     => $location,
            'entPhysicalName'           => $shortlocation,
            'entPhysicalContainedIn'    => $parent,
            'entPhysicalParentRelPos'   => '-1',
        );

        // Add to the DB and Array.
        $id = dbInsert($insert, 'entPhysical');
        $entphysical[$location] = dbFetchRow('SELECT * FROM entPhysical WHERE entPhysical_id=?', array($id));
        return $index;
    } // end if - Level 1
} // end function


/* idea from http://php.net/manual/en/function.hex2bin.php comments */
function hex2bin_compat($str)
{
    if (strlen($str) % 2 !== 0) {
        trigger_error(__FUNCTION__.'(): Hexadecimal input string must have an even length', E_USER_WARNING);
    }
    return pack("H*", $str);
}

if (!function_exists('hex2bin')) {
    // This is only a hack
    function hex2bin($str)
    {
        return hex2bin_compat($str);
    }
}

function q_bridge_bits2indices($hex_data)
{
    /* convert hex string to an array of 1-based indices of the nonzero bits
     * ie. '9a00' -> '100110100000' -> array(1, 4, 5, 7)
    */
    $hex_data = str_replace(' ', '', $hex_data);
    $value = hex2bin($hex_data);
    $length = strlen($value);
    $indices = array();
    for ($i = 0; $i < $length; $i++) {
        $byte = ord($value[$i]);
        for ($j = 7; $j >= 0; $j--) {
            if ($byte & (1 << $j)) {
                $indices[] = 8*$i + 8-$j;
            }
        }
    }
    return $indices;
}

/**
 * @param array $device
 * @param int|string $raw_value The value returned from snmp
 * @param int $capacity the normalized capacity
 * @return int the toner level as a percentage
 */
function get_toner_levels($device, $raw_value, $capacity)
{
    // -3 means some toner is left
    if ($raw_value == '-3') {
        return 50;
    }

    // -2 means unknown
    if ($raw_value == '-2') {
        return false;
    }

    // -1 mean no restrictions
    if ($raw_value == '-1') {
        return 0;  // FIXME: is 0 what we should return?
    }

    // Non-standard snmp values
    if ($device['os'] == 'ricoh' || $device['os'] == 'nrg' || $device['os'] == 'lanier') {
        if ($raw_value == '-100') {
            return 0;
        }
    } elseif ($device['os'] == 'brother') {
        if (!str_contains($device['hardware'], 'MFC-L8850')) {
            switch ($raw_value) {
                case '0':
                    return 100;
                case '1':
                    return 5;
                case '2':
                    return 0;
                case '3':
                    return 1;
            }
        }
    }

    return round($raw_value / $capacity * 100);
}

/**
 * Intialize global stat arrays
 */
function initStats()
{
    global $snmp_stats, $rrd_stats;
    global $snmp_stats_last, $rrd_stats_last;

    if (!isset($snmp_stats, $rrd_stats)) {
        $snmp_stats = array(
            'ops' => array(
                'snmpget' => 0,
                'snmpgetnext' => 0,
                'snmpwalk' => 0,
            ),
            'time' => array(
                'snmpget' => 0.0,
                'snmpgetnext' => 0.0,
                'snmpwalk' => 0.0,
            )
        );
        $snmp_stats_last = $snmp_stats;

        $rrd_stats = array(
            'ops' => array(
                'update' => 0,
                'create' => 0,
                'other' => 0,
            ),
            'time' => array(
                'update' => 0.0,
                'create' => 0.0,
                'other' => 0.0,
            ),
        );
        $rrd_stats_last = $rrd_stats;
    }
}

/**
 * Print out the stats totals since the last time this function was called
 *
 * @param bool $update_only Only update the stats checkpoint, don't print them
 */
function printChangedStats($update_only = false)
{
    global $snmp_stats, $db_stats, $rrd_stats;
    global $snmp_stats_last, $db_stats_last, $rrd_stats_last;

    if (!$update_only) {
        printf(
            ">> SNMP: [%d/%.2fs] MySQL: [%d/%.2fs] RRD: [%d/%.2fs]\n",
            array_sum($snmp_stats['ops']) - array_sum($snmp_stats_last['ops']),
            array_sum($snmp_stats['time']) - array_sum($snmp_stats_last['time']),
            array_sum($db_stats['ops']) - array_sum($db_stats_last['ops']),
            array_sum($db_stats['time']) - array_sum($db_stats_last['time']),
            array_sum($rrd_stats['ops']) - array_sum($rrd_stats_last['ops']),
            array_sum($rrd_stats['time']) - array_sum($rrd_stats_last['time'])
        );
    }

    // make a new checkpoint
    $snmp_stats_last = $snmp_stats;
    $db_stats_last = $db_stats;
    $rrd_stats_last = $rrd_stats;
}

/**
 * Print global stat arrays
 */
function printStats()
{
    global $snmp_stats, $db_stats, $rrd_stats;

    if ($snmp_stats) {
        printf(
            "SNMP [%d/%.2fs]: Get[%d/%.2fs] Getnext[%d/%.2fs] Walk[%d/%.2fs]\n",
            array_sum($snmp_stats['ops']),
            array_sum($snmp_stats['time']),
            $snmp_stats['ops']['snmpget'],
            $snmp_stats['time']['snmpget'],
            $snmp_stats['ops']['snmpgetnext'],
            $snmp_stats['time']['snmpgetnext'],
            $snmp_stats['ops']['snmpwalk'],
            $snmp_stats['time']['snmpwalk']
        );
    }

    if ($db_stats) {
        printf(
            "MySQL [%d/%.2fs]: Cell[%d/%.2fs] Row[%d/%.2fs] Rows[%d/%.2fs] Column[%d/%.2fs] Update[%d/%.2fs] Insert[%d/%.2fs] Delete[%d/%.2fs]\n",
            array_sum($db_stats['ops']),
            array_sum($db_stats['time']),
            $db_stats['ops']['fetchcell'],
            $db_stats['time']['fetchcell'],
            $db_stats['ops']['fetchrow'],
            $db_stats['time']['fetchrow'],
            $db_stats['ops']['fetchrows'],
            $db_stats['time']['fetchrows'],
            $db_stats['ops']['fetchcolumn'],
            $db_stats['time']['fetchcolumn'],
            $db_stats['ops']['update'],
            $db_stats['time']['update'],
            $db_stats['ops']['insert'],
            $db_stats['time']['insert'],
            $db_stats['ops']['delete'],
            $db_stats['time']['delete']
        );
    }

    if ($rrd_stats) {
        printf(
            "RRD [%d/%.2fs]: Update[%d/%.2fs] Create [%d/%.2fs] Other[%d/%.2fs]\n",
            array_sum($rrd_stats['ops']),
            array_sum($rrd_stats['time']),
            $rrd_stats['ops']['update'],
            $rrd_stats['time']['update'],
            $rrd_stats['ops']['create'],
            $rrd_stats['time']['create'],
            $rrd_stats['ops']['other'],
            $rrd_stats['time']['other']
        );
    }
}

/**
 * Update statistics for rrd operations
 *
 * @param string $stat create, update, and other
 * @param float $start_time The time the operation started with 'microtime(true)'
 * @return float  The calculated run time
 */
function recordRrdStatistic($stat, $start_time)
{
    global $rrd_stats;
    initStats();

    $stat = ($stat == 'update' || $stat == 'create') ? $stat : 'other';

    $runtime = microtime(true) - $start_time;
    $rrd_stats['ops'][$stat]++;
    $rrd_stats['time'][$stat] += $runtime;

    return $runtime;
}

/**
 * @param string $stat snmpget, snmpwalk
 * @param float $start_time The time the operation started with 'microtime(true)'
 * @return float  The calculated run time
 */
function recordSnmpStatistic($stat, $start_time)
{
    global $snmp_stats;
    initStats();

    $runtime = microtime(true) - $start_time;
    $snmp_stats['ops'][$stat]++;
    $snmp_stats['time'][$stat] += $runtime;
    return $runtime;
}

function runTraceroute($device)
{
    $address_family = snmpTransportToAddressFamily($device['transport']);
    $trace_name = $address_family == 'ipv6' ? 'traceroute6' : 'traceroute';
    $trace_path = Config::get($trace_name, $trace_name);
    $process = new Process([$trace_path, '-q', '1', '-w', '1', $device['hostname']]);
    $process->run();
    if ($process->isSuccessful()) {
        return ['traceroute' => $process->getOutput()];
    }
    return ['output' => $process->getErrorOutput()];
}

/**
 * @param $device
 * @param bool $record_perf
 * @return array
 */
function device_is_up($device, $record_perf = false)
{
    $address_family = snmpTransportToAddressFamily($device['transport']);
    $ping_response = isPingable($device['hostname'], $address_family, $device['attribs']);
    $device_perf              = $ping_response['db'];
    $device_perf['device_id'] = $device['device_id'];
    $device_perf['timestamp'] = array('NOW()');

    if ($record_perf === true && can_ping_device($device['attribs'])) {
        $trace_debug = [];
        if ($ping_response['result'] === false && Config::get('debug.run_trace', false)) {
            $trace_debug = runTraceroute($device);
        }
        $device_perf['debug'] = json_encode($trace_debug);
        dbInsert($device_perf, 'device_perf');
    }
    $response              = array();
    $response['ping_time'] = $ping_response['last_ping_timetaken'];
    if ($ping_response['result']) {
        if ($device['snmp_disable'] || isSNMPable($device)) {
            $response['status']        = '1';
            $response['status_reason'] = '';
        } else {
            echo 'SNMP Unreachable';
            $response['status']        = '0';
            $response['status_reason'] = 'snmp';
        }
    } else {
        echo 'Unpingable';
        $response['status']        = '0';
        $response['status_reason'] = 'icmp';
    }

    if ($device['status'] != $response['status'] || $device['status_reason'] != $response['status_reason']) {
        dbUpdate(
            array('status' => $response['status'], 'status_reason' => $response['status_reason']),
            'devices',
            'device_id=?',
            array($device['device_id'])
        );

        if ($response['status']) {
            $type = 'up';
            $reason = $device['status_reason'];
        } else {
            $type = 'down';
            $reason = $response['status_reason'];
        }

        log_event('Device status changed to ' . ucfirst($type) . " from $reason check.", $device, $type);
    }
    return $response;
}

function update_device_logo(&$device)
{
    $icon = getImageName($device, false);
    if ($icon != $device['icon']) {
        log_event('Device Icon changed ' . $device['icon'] . " => $icon", $device, 'system', 3);
        $device['icon'] = $icon;
        dbUpdate(array('icon' => $icon), 'devices', 'device_id=?', array($device['device_id']));
        echo "Changed Icon! : $icon\n";
    }
}

/**
 * Function to generate PeeringDB Cache
 */
function cache_peeringdb()
{
    global $config;
    if ($config['peeringdb']['enabled'] === true) {
        $peeringdb_url = 'https://peeringdb.com/api';
        // We cache for 71 hours
        $cached = dbFetchCell("SELECT count(*) FROM `pdb_ix` WHERE (UNIX_TIMESTAMP() - timestamp) < 255600");
        if ($cached == 0) {
            $rand = rand(3, 30);
            echo "No cached PeeringDB data found, sleeping for $rand seconds" . PHP_EOL;
            sleep($rand);
            $peer_keep = [];
            $ix_keep = [];
            foreach (dbFetchRows("SELECT `bgpLocalAs` FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 GROUP BY `bgpLocalAs`") as $as) {
                $asn = $as['bgpLocalAs'];
                $get = Requests::get($peeringdb_url . '/net?depth=2&asn=' . $asn, array(), array('proxy' => get_proxy()));
                $json_data = $get->body;
                $data = json_decode($json_data);
                $ixs = $data->{'data'}{0}->{'netixlan_set'};
                foreach ($ixs as $ix) {
                    $ixid = $ix->{'ix_id'};
                    $tmp_ix = dbFetchRow("SELECT * FROM `pdb_ix` WHERE `ix_id` = ? AND asn = ?", array($ixid, $asn));
                    if ($tmp_ix) {
                        $pdb_ix_id = $tmp_ix['pdb_ix_id'];
                        $update = array('name' => $ix->{'name'}, 'timestamp' => time());
                        dbUpdate($update, 'pdb_ix', '`ix_id` = ? AND `asn` = ?', array($ixid, $asn));
                    } else {
                        $insert = array(
                            'ix_id' => $ixid,
                            'name' => $ix->{'name'},
                            'asn' => $asn,
                            'timestamp' => time()
                        );
                        $pdb_ix_id = dbInsert($insert, 'pdb_ix');
                    }
                    $ix_keep[] = $pdb_ix_id;
                    $get_ix = Requests::get("$peeringdb_url/netixlan?ix_id=$ixid", array(), array('proxy' => get_proxy()));
                    $ix_json = $get_ix->body;
                    $ix_data = json_decode($ix_json);
                    $peers = $ix_data->{'data'};
                    foreach ($peers as $index => $peer) {
                        $peer_name = get_astext($peer->{'asn'});
                        $tmp_peer = dbFetchRow("SELECT * FROM `pdb_ix_peers` WHERE `peer_id` = ? AND `ix_id` = ?", array($peer->{'id'}, $ixid));
                        if ($tmp_peer) {
                            $peer_keep[] = $tmp_peer['pdb_ix_peers_id'];
                            $update = array(
                                'remote_asn'     => $peer->{'asn'},
                                'remote_ipaddr4'  => $peer->{'ipaddr4'},
                                'remote_ipaddr6' => $peer->{'ipaddr6'},
                                'name'           => $peer_name,
                            );
                            dbUpdate($update, 'pdb_ix_peers', '`pdb_ix_peers_id` = ?', array($tmp_peer['pdb_ix_peers_id']));
                        } else {
                            $peer_insert = array(
                                'ix_id'          => $ixid,
                                'peer_id'        => $peer->{'id'},
                                'remote_asn'     => $peer->{'asn'},
                                'remote_ipaddr4' => $peer->{'ipaddr4'},
                                'remote_ipaddr6' => $peer->{'ipaddr6'},
                                'name'           => $peer_name,
                                'timestamp'      => time()
                            );
                            $peer_keep[] = dbInsert($peer_insert, 'pdb_ix_peers');
                        }
                    }
                }
            }

            // cleanup
            if (empty($peer_keep)) {
                dbDelete('pdb_ix_peers');
            } else {
                dbDelete('pdb_ix_peers', "`pdb_ix_peers_id` NOT IN " . dbGenPlaceholders(count($peer_keep)), $peer_keep);
            }
            if (empty($ix_keep)) {
                dbDelete('pdb_ix');
            } else {
                dbDelete('pdb_ix', "`pdb_ix_id` NOT IN " . dbGenPlaceholders(count($ix_keep)), $ix_keep);
            }
        } else {
            echo "Cached PeeringDB data found....." . PHP_EOL;
        }
    } else {
        echo 'Peering DB integration disabled' . PHP_EOL;
    }
}

/**
 * Dump the database schema to an array.
 * The top level will be a list of tables
 * Each table contains the keys Columns and Indexes.
 *
 * Each entry in the Columns array contains these keys: Field, Type, Null, Default, Extra
 * Each entry in the Indexes array contains these keys: Name, Columns(array), Unique
 *
 * @return array
 */
function dump_db_schema()
{
    global $config;

    $output = array();
    $db_name = dbFetchCell('SELECT DATABASE()');

    foreach (dbFetchRows("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name' ORDER BY TABLE_NAME;") as $table) {
        $table = $table['TABLE_NAME'];
        foreach (dbFetchRows("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME='$table'") as $data) {
            $def = array(
                'Field'   => $data['COLUMN_NAME'],
                'Type'    => $data['COLUMN_TYPE'],
                'Null'    => $data['IS_NULLABLE'] === 'YES',
                'Extra'   => str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $data['EXTRA']),
            );

            if (isset($data['COLUMN_DEFAULT']) && $data['COLUMN_DEFAULT'] != 'NULL') {
                $default = trim($data['COLUMN_DEFAULT'], "'");
                $def['Default'] = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $default);
            }

            $output[$table]['Columns'][] = $def;
        }

        foreach (dbFetchRows("SHOW INDEX FROM `$table`") as $key) {
            $key_name = $key['Key_name'];
            if (isset($output[$table]['Indexes'][$key_name])) {
                $output[$table]['Indexes'][$key_name]['Columns'][] = $key['Column_name'];
            } else {
                $output[$table]['Indexes'][$key_name] = array(
                    'Name'    => $key['Key_name'],
                    'Columns' => array($key['Column_name']),
                    'Unique'  => !$key['Non_unique'],
                    'Type'    => $key['Index_type'],
                );
            }
        }
    }
    return $output;
}






/**
 * Get an array of the schema files.
 * schema_version => full_file_name
 *
 * @return mixed
 */
function get_schema_list()
{
    global $config;

    // glob returns an array sorted by filename
    $files = glob($config['install_dir'].'/sql-schema/*.sql');

    // set the keys to the db schema version
    return array_reduce($files, function ($array, $file) {
        $array[basename($file, '.sql')] = $file;
        return $array;
    }, array());
}

/**
 * Get the current database schema, will return 0 if there is no schema.
 *
 * @return int
 */
function get_db_schema()
{
    try {
        $db = \LibreNMS\DB\Eloquent::DB();
        if ($db) {
            return $db->table('dbSchema')
                ->orderBy('version', 'DESC')
                ->value('version');
        }
    } catch (PDOException $e) {
        // return default
    }

    return 0;
}

/**
 * Check if the database schema is up to date.
 *
 * @return bool
 */
function db_schema_is_current()
{
    $current = get_db_schema();

    $schemas = get_schema_list();
    end($schemas);
    $latest = key($schemas);

    return $current >= $latest;
}

/**
 * @param $device
 * @return int|null
 */
function get_device_oid_limit($device)
{
    // device takes priority
    if ($device['snmp_max_oid'] > 0) {
        return $device['snmp_max_oid'];
    }

    // then os
    $os_max = Config::getOsSetting($device['os'], 'snmp_max_oid', 0);
    if ($os_max > 0) {
        return $os_max;
    }

    // then global
    $global_max = Config::get('snmp.max_oid', 10);
    return $global_max > 0 ? $global_max : 10;
}

/**
 * Strip out non-numeric characters
 */
function return_num($entry)
{
    if (!is_numeric($entry)) {
        preg_match('/-?\d*\.?\d+/', $entry, $num_response);
        return $num_response[0];
    }
}

/**
 * If Distributed, create a lock, then purge the mysql table
 *
 * @param string $table
 * @param string $sql
 * @return int exit code
 */
function lock_and_purge($table, $sql)
{
    try {
        $purge_name = $table . '_purge';

        if (Config::get('distributed_poller')) {
            MemcacheLock::lock($purge_name, 0, 86000);
        }
        $purge_days = Config::get($purge_name);

        $name = str_replace('_', ' ', ucfirst($table));
        if (is_numeric($purge_days)) {
            if (dbDelete($table, $sql, array($purge_days))) {
                echo "$name cleared for entries over $purge_days days\n";
            }
        }
        return 0;
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        return -1;
    }
}

/**
 * Convert space separated hex OID content to character
 *
 * @param string $hex_string
 * @return string $chr_string
 */

function hexbin($hex_string)
{
    $chr_string = '';
    foreach (explode(' ', $hex_string) as $a) {
        $chr_string .= chr(hexdec($a));
    }
    return $chr_string;
}

/**
 * Check if disk is valid to poll.
 * Settings: bad_disk_regexp
 *
 * @param array $disk
 * @param array $device
 * @return bool
 */
function is_disk_valid($disk, $device)
{
    foreach (Config::getCombined($device['os'], 'bad_disk_regexp') as $bir) {
        if (preg_match($bir ."i", $disk['diskIODevice'])) {
            d_echo("Ignored Disk: {$disk['diskIODevice']} (matched: $bir)\n");
            return false;
        }
    }
    return true;
}


/**
 * Queues a hostname to be refreshed by Oxidized
 * Settings: oxidized.url
 *
 * @param string $hostname
 * @param string $msg
 * @param string $username
 * @return bool
 */
function oxidized_node_update($hostname, $msg, $username = 'not_provided')
{
    // Work around https://github.com/rack/rack/issues/337
    $msg = str_replace("%", "", $msg);
    $postdata = ["user" => $username, "msg" => $msg];
    $oxidized_url = Config::get('oxidized.url');
    if (!empty($oxidized_url)) {
        Requests::put("$oxidized_url/node/next/$hostname", [], json_encode($postdata), ['proxy' => get_proxy()]);
        return true;
    }
    return false;
}//end oxidized_node_update()
