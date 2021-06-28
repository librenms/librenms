<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\HostUnreachablePingException;
use LibreNMS\Exceptions\InvalidPortAssocModeException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Fping;
use LibreNMS\Modules\Core;
use LibreNMS\Util\Debug;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Process\Process;

function array_sort_by_column($array, $on, $order = SORT_ASC)
{
    $new_array = [];
    $sortable_array = [];

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
            if (Str::contains($module, '/')) {
                [$module, $submodule] = explode('/', $module, 2);
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
    $fd = fopen(Config::get('log_file'), 'a');
    fputs($fd, $string . "\n");
    fclose($fd);
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
    foreach ((array) $regexes as $regex) {
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
    // handle PHP8 change to implicit casting
    if (is_numeric($a) || is_numeric($b)) {
        $a = cast_number($a);
        $b = is_array($b) ? $b : cast_number($b);
    }

    switch ($comparison) {
        case '=':
            return $a == $b;
        case '!=':
            return $a != $b;
        case '==':
            return $a === $b;
        case '!==':
            return $a !== $b;
        case '>=':
            return $a >= $b;
        case '<=':
            return $a <= $b;
        case '>':
            return $a > $b;
        case '<':
            return $a < $b;
        case 'contains':
            return Str::contains($a, $b);
        case 'not_contains':
            return ! Str::contains($a, $b);
        case 'starts':
            return Str::startsWith($a, $b);
        case 'not_starts':
            return ! Str::startsWith($a, $b);
        case 'ends':
            return Str::endsWith($a, $b);
        case 'not_ends':
            return ! Str::endsWith($a, $b);
        case 'regex':
            return (bool) preg_match($b, $a);
        case 'not regex':
            return ! ((bool) preg_match($b, $a));
        case 'in_array':
            return in_array($a, $b);
        case 'not_in_array':
            return ! in_array($a, $b);
        case 'exists':
            return isset($a) == $b;
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

/**
 * @param $device
 * @return string the logo image path for this device. Images are often wide, not square.
 */
function getLogo($device)
{
    $img = getImageName($device, true, 'images/logos/');
    if (! Str::startsWith($img, 'generic')) {
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
    $tag = '<img src="' . url(getLogo($device)) . '" title="' . getImageTitle($device) . '"';
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
    return $device['icon'] ? str_replace(['.svg', '.png'], '', $device['icon']) : $device['os'];
}

function getImageName($device, $use_database = true, $dir = 'images/os/')
{
    return \LibreNMS\Util\Url::findOsImage($device['os'], $device['features'], $use_database ? $device['icon'] : null, $dir);
}

function renamehost($id, $new, $source = 'console')
{
    $host = gethostbyid($id);

    if (! is_dir(Rrd::dirFromHost($new)) && rename(Rrd::dirFromHost($host), Rrd::dirFromHost($new)) === true) {
        dbUpdate(['hostname' => $new, 'ip' => null], 'devices', 'device_id=?', [$id]);
        log_event("Hostname changed -> $new ($source)", $id, 'system', 3);

        return '';
    }

    log_event("Renaming of $host failed", $id, 'system', 5);

    return "Renaming of $host failed\n";
}

function device_discovery_trigger($id)
{
    if (App::runningInConsole() === false) {
        ignore_user_abort(true);
        set_time_limit(0);
    }

    $update = dbUpdate(['last_discovered' => ['NULL']], 'devices', '`device_id` = ?', [$id]);
    if (! empty($update) || $update == '0') {
        $message = 'Device will be rediscovered';
    } else {
        $message = 'Error rediscovering device';
    }

    return ['status'=> $update, 'message' => $message];
}

function delete_device($id)
{
    if (App::runningInConsole() === false) {
        ignore_user_abort(true);
        set_time_limit(0);
    }

    $ret = '';

    $host = dbFetchCell('SELECT hostname FROM devices WHERE device_id = ?', [$id]);
    if (empty($host)) {
        return 'No such host.';
    }

    // Remove IPv4/IPv6 addresses before removing ports as they depend on port_id
    dbQuery('DELETE `ipv4_addresses` FROM `ipv4_addresses` INNER JOIN `ports` ON `ports`.`port_id`=`ipv4_addresses`.`port_id` WHERE `device_id`=?', [$id]);
    dbQuery('DELETE `ipv6_addresses` FROM `ipv6_addresses` INNER JOIN `ports` ON `ports`.`port_id`=`ipv6_addresses`.`port_id` WHERE `device_id`=?', [$id]);

    //Remove IsisAdjacencies
    \App\Models\IsisAdjacency::where('device_id', $id)->delete();

    //Remove Outages
    \App\Models\Availability::where('device_id', $id)->delete();
    \App\Models\DeviceOutage::where('device_id', $id)->delete();

    \App\Models\Port::where('device_id', $id)
        ->with('device')
        ->select(['port_id', 'device_id', 'ifIndex', 'ifName', 'ifAlias', 'ifDescr'])
        ->chunk(100, function ($ports) use (&$ret) {
            foreach ($ports as $port) {
                $port->delete();
                $ret .= "Removed interface $port->port_id (" . $port->getLabel() . ")\n";
            }
        });

    // Remove sensors manually due to constraints
    foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `device_id` = ?', [$id]) as $sensor) {
        $sensor_id = $sensor['sensor_id'];
        dbDelete('sensors_to_state_indexes', '`sensor_id` = ?', [$sensor_id]);
    }
    $fields = ['device_id', 'host'];

    $db_name = dbFetchCell('SELECT DATABASE()');
    foreach ($fields as $field) {
        foreach (dbFetch('SELECT TABLE_NAME FROM information_schema.columns WHERE table_schema = ? AND column_name = ?', [$db_name, $field]) as $table) {
            $table = $table['TABLE_NAME'];
            $entries = (int) dbDelete($table, "`$field` =  ?", [$id]);
            if ($entries > 0 && Debug::isEnabled()) {
                $ret .= "$field@$table = #$entries\n";
            }
        }
    }

    $ex = shell_exec("bash -c '( [ ! -d " . trim(Rrd::dirFromHost($host)) . ' ] || rm -vrf ' . trim(Rrd::dirFromHost($host)) . " 2>&1 ) && echo -n OK'");
    $tmp = explode("\n", $ex);
    if ($tmp[sizeof($tmp) - 1] != 'OK') {
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
 * @param int $port the port to connect to for snmp
 * @param string $transport udp or tcp
 * @param string $poller_group the poller group this device will belong to
 * @param bool $force_add add even if the device isn't reachable
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
function addHost($host, $snmp_version = '', $port = 161, $transport = 'udp', $poller_group = '0', $force_add = false, $port_assoc_mode = 'ifIndex', $additional = [])
{
    // Test Database Exists
    if (host_exists($host)) {
        throw new HostExistsException("Already have host $host");
    }

    // Valid port assoc mode
    if (! in_array($port_assoc_mode, get_port_assoc_modes())) {
        throw new InvalidPortAssocModeException("Invalid port association_mode '$port_assoc_mode'. Valid modes are: " . join(', ', get_port_assoc_modes()));
    }

    // check if we have the host by IP
    $overwrite_ip = null;
    if (! empty($additional['overwrite_ip'])) {
        $overwrite_ip = $additional['overwrite_ip'];
        $ip = $overwrite_ip;
    } elseif (Config::get('addhost_alwayscheckip') === true) {
        $ip = gethostbyname($host);
    } else {
        $ip = $host;
    }
    if ($force_add !== true && $device = device_has_ip($ip)) {
        $message = "Cannot add $host, already have device with this IP $ip";
        if ($ip != $device->hostname) {
            $message .= " ($device->hostname)";
        }
        $message .= '. You may force add to ignore this.';
        throw new HostIpExistsException($message);
    }

    // Test reachability
    if (! $force_add) {
        $address_family = snmpTransportToAddressFamily($transport);
        $ping_result = isPingable($ip, $address_family);
        if (! $ping_result['result']) {
            throw new HostUnreachablePingException("Could not ping $host");
        }
    }

    // if $snmpver isn't set, try each version of snmp
    if (empty($snmp_version)) {
        $snmpvers = Config::get('snmp.version');
    } else {
        $snmpvers = [$snmp_version];
    }

    if (isset($additional['snmp_disable']) && $additional['snmp_disable'] == 1) {
        return createHost($host, '', $snmp_version, $port, $transport, [], $poller_group, 1, true, $overwrite_ip, $additional);
    }
    $host_unreachable_exception = new HostUnreachableException("Could not connect to $host, please check the snmp details and snmp reachability");
    // try different snmp variables to add the device
    foreach ($snmpvers as $snmpver) {
        if ($snmpver === 'v3') {
            // Try each set of parameters from config
            foreach (Config::get('snmp.v3') as $v3) {
                $device = deviceArray($host, null, $snmpver, $port, $transport, $v3, $port_assoc_mode, $overwrite_ip);
                if ($force_add === true || isSNMPable($device)) {
                    return createHost($host, null, $snmpver, $port, $transport, $v3, $poller_group, $port_assoc_mode, $force_add, $overwrite_ip);
                } else {
                    $host_unreachable_exception->addReason("SNMP $snmpver: No reply with credentials " . $v3['authname'] . '/' . $v3['authlevel']);
                }
            }
        } elseif ($snmpver === 'v2c' || $snmpver === 'v1') {
            // try each community from config
            foreach (Config::get('snmp.community') as $community) {
                $device = deviceArray($host, $community, $snmpver, $port, $transport, null, $port_assoc_mode, $overwrite_ip);

                if ($force_add === true || isSNMPable($device)) {
                    return createHost($host, $community, $snmpver, $port, $transport, [], $poller_group, $port_assoc_mode, $force_add, $overwrite_ip);
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
        $additional['os'] = 'ping';

        return createHost($host, '', $snmp_version, $port, $transport, [], $poller_group, 1, true, $overwrite_ip, $additional);
    }
    throw $host_unreachable_exception;
}

function deviceArray($host, $community, $snmpver, $port = 161, $transport = 'udp', $v3 = [], $port_assoc_mode = 'ifIndex', $overwrite_ip = null)
{
    $device = [];
    $device['hostname'] = $host;
    $device['overwrite_ip'] = $overwrite_ip;
    $device['port'] = $port;
    $device['transport'] = $transport;

    /* Get port_assoc_mode id if neccessary
     * We can work with names of IDs here */
    if (! is_int($port_assoc_mode)) {
        $port_assoc_mode = get_port_assoc_mode_id($port_assoc_mode);
    }
    $device['port_association_mode'] = $port_assoc_mode;

    $device['snmpver'] = $snmpver;
    if ($snmpver === 'v2c' or $snmpver === 'v1') {
        $device['community'] = $community;
    } elseif ($snmpver === 'v3') {
        $device['authlevel'] = $v3['authlevel'];
        $device['authname'] = $v3['authname'];
        $device['authpass'] = $v3['authpass'];
        $device['authalgo'] = $v3['authalgo'];
        $device['cryptopass'] = $v3['cryptopass'];
        $device['cryptoalgo'] = $v3['cryptoalgo'];
    }

    return $device;
}//end deviceArray()

function isSNMPable($device)
{
    $pos = snmp_check($device);
    if ($pos === true) {
        return true;
    } else {
        $pos = snmp_get($device, 'sysObjectID.0', '-Oqv', 'SNMPv2-MIB');
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
            'last_ping_timetaken' => 0,
        ];
    }

    $status = app()->make(Fping::class)->ping(
        $hostname,
        Config::get('fping_options.count', 3),
        Config::get('fping_options.interval', 500),
        Config::get('fping_options.timeout', 500),
        $address_family
    );

    if ($status['dup'] > 0) {
        Log::event('Duplicate ICMP response detected! This could indicate a network issue.', getidbyname($hostname), 'icmp', 4);
        $status['exitcode'] = 0;   // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
    }

    return [
        'result' => ($status['exitcode'] == 0 && $status['loss'] < 100),
        'last_ping_timetaken' => $status['avg'],
        'db' => array_intersect_key($status, array_flip(['xmt', 'rcv', 'loss', 'min', 'max', 'avg'])),
    ];
}

function getpollergroup($poller_group = '0')
{
    //Is poller group an integer
    if (is_int($poller_group) || ctype_digit($poller_group)) {
        return $poller_group;
    } else {
        //Check if it contains a comma
        if (strpos($poller_group, ',') !== false) {
            //If it has a comma use the first element as the poller group
            $poller_group_array = explode(',', $poller_group);

            return getpollergroup($poller_group_array[0]);
        } else {
            if (Config::get('distributed_poller_group')) {
                //If not use the poller's group from the config
                return getpollergroup(Config::get('distributed_poller_group'));
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
    $v3 = [],
    $poller_group = 0,
    $port_assoc_mode = 'ifIndex',
    $force_add = false,
    $overwrite_ip = null,
    $additional = []
) {
    $host = trim(strtolower($host));

    $poller_group = getpollergroup($poller_group);

    /* Get port_assoc_mode id if necessary
     * We can work with names of IDs here */
    if (! is_int($port_assoc_mode)) {
        $port_assoc_mode = get_port_assoc_mode_id($port_assoc_mode);
    }

    $device = [
        'hostname' => $host,
        'overwrite_ip' => $overwrite_ip,
        'sysName' => $additional['sysName'] ?? $host,
        'os' => $additional['os'] ?? 'generic',
        'hardware' => $additional['hardware'] ?? null,
        'community' => $community,
        'port' => $port,
        'transport' => $transport,
        'status' => '1',
        'snmpver' => $snmpver,
        'poller_group' => $poller_group,
        'status_reason' => '',
        'port_association_mode' => $port_assoc_mode,
        'snmp_disable' => $additional['snmp_disable'] ?? 0,
    ];

    $device = array_merge($device, $v3);  // merge v3 settings

    if ($force_add !== true) {
        $device['os'] = Core::detectOS($device);

        $snmphost = snmp_get($device, 'sysName.0', '-Oqv', 'SNMPv2-MIB');
        if (host_exists($host, $snmphost)) {
            throw new HostExistsException("Already have host $host ($snmphost) due to duplicate sysName");
        }
    }

    $device_id = dbInsert($device, 'devices');
    if ($device_id) {
        return $device_id;
    }

    throw new \Exception('Failed to add host to the database, please run ./validate.php');
}

function isDomainResolves($domain)
{
    if (gethostbyname($domain) != $domain) {
        return true;
    }

    $records = dns_get_record($domain);  // returns array or false

    return ! empty($records);
}

function match_network($nets, $ip, $first = false)
{
    $return = false;
    if (! is_array($nets)) {
        $nets = [$nets];
    }
    foreach ($nets as $net) {
        $rev = (preg_match("/^\!/", $net)) ? true : false;
        $net = preg_replace("/^\!/", '', $net);
        $ip_arr = explode('/', $net);
        $net_long = ip2long($ip_arr[0]);
        $x = ip2long($ip_arr[1]);
        $mask = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
        $ip_long = ip2long($ip);
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
    // Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
    // < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
    $ipv6 = array_slice(explode('.', $ipv6_snmp), -16);
    $ipv6_2 = [];

    for ($i = 0; $i <= 15; $i++) {
        $ipv6[$i] = zeropad(dechex($ipv6[$i]));
    }
    for ($i = 0; $i <= 15; $i += 2) {
        $ipv6_2[] = $ipv6[$i] . $ipv6[$i + 1];
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
    if (! empty($result[0]['txt'])) {
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
    // handle legacy device array
    if (is_array($device) && isset($device['device_id'])) {
        $device = $device['device_id'];
    }

    Log::event($text, $device, $type, $severity, $reference);
}

// Parse string with emails. Return array with email (as key) and name (as value)
function parse_email($emails)
{
    $result = [];
    $regex = '/^[\"\']?([^\"\']+)[\"\']?\s{0,}<([^@]+@[^>]+)>$/';
    if (is_string($emails)) {
        $emails = preg_split('/[,;]\s{0,}/', $emails);
        foreach ($emails as $email) {
            if (preg_match($regex, $email, $out, PREG_OFFSET_CAPTURE)) {
                $result[$out[2][0]] = $out[1][0];
            } else {
                if (strpos($email, '@')) {
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
    if (is_array($emails) || ($emails = parse_email($emails))) {
        d_echo("Attempting to email $subject to: " . implode('; ', array_keys($emails)) . PHP_EOL);
        $mail = new PHPMailer(true);
        try {
            $mail->Hostname = php_uname('n');

            foreach (parse_email(Config::get('email_from')) as $from => $from_name) {
                $mail->setFrom($from, $from_name);
            }
            foreach ($emails as $email => $email_name) {
                $mail->addAddress($email, $email_name);
            }
            $mail->Subject = $subject;
            $mail->XMailer = Config::get('project_name');
            $mail->CharSet = 'utf-8';
            $mail->WordWrap = 76;
            $mail->Body = $message;
            if ($html) {
                $mail->isHTML(true);
            }
            switch (strtolower(trim(Config::get('email_backend')))) {
                case 'sendmail':
                    $mail->Mailer = 'sendmail';
                    $mail->Sendmail = Config::get('email_sendmail_path');
                    break;
                case 'smtp':
                    $mail->isSMTP();
                    $mail->Host = Config::get('email_smtp_host');
                    $mail->Timeout = Config::get('email_smtp_timeout');
                    $mail->SMTPAuth = Config::get('email_smtp_auth');
                    $mail->SMTPSecure = Config::get('email_smtp_secure');
                    $mail->Port = Config::get('email_smtp_port');
                    $mail->Username = Config::get('email_smtp_username');
                    $mail->Password = Config::get('email_smtp_password');
                    $mail->SMTPAutoTLS = Config::get('email_auto_tls');
                    $mail->SMTPDebug = false;
                    break;
                default:
                    $mail->Mailer = 'mail';
                    break;
            }
            $mail->send();

            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return $e->errorMessage();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    return 'No contacts found';
}

function hex2str($hex)
{
    $string = '';

    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $string .= chr(hexdec(substr($hex, $i, 2)));
    }

    return $string;
}

// Convert an SNMP hex string to regular string
function snmp_hexstring($hex)
{
    return hex2str(str_replace(' ', '', str_replace(' 00', '', $hex)));
}

// Check if the supplied string is an SNMP hex string
function isHexString($str)
{
    return (bool) preg_match('/^[a-f0-9][a-f0-9]( [a-f0-9][a-f0-9])*$/is', trim($str));
}

// Include all .inc.php files in $dir
function include_dir($dir, $regex = '')
{
    global $device, $valid;

    if ($regex == '') {
        $regex = "/\.inc\.php$/";
    }

    if ($handle = opendir(Config::get('install_dir') . '/' . $dir)) {
        while (false !== ($file = readdir($handle))) {
            if (filetype(Config::get('install_dir') . '/' . $dir . '/' . $file) == 'file' && preg_match($regex, $file)) {
                d_echo('Including: ' . Config::get('install_dir') . '/' . $dir . '/' . $file . "\n");

                include Config::get('install_dir') . '/' . $dir . '/' . $file;
            }
        }

        closedir($handle);
    }
}

/**
 * Check if port is valid to poll.
 * Settings: empty_ifdescr, good_if, bad_if, bad_if_regexp, bad_ifname_regexp, bad_ifalias_regexp, bad_iftype, bad_ifoperstatus
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
        if (! Config::getOsSetting($device['os'], 'empty_ifdescr', Config::get('empty_ifdescr', false))) {
            d_echo("ignored: empty ifDescr\n");

            return false;
        }
    }

    $ifDescr = $port['ifDescr'];
    $ifName = $port['ifName'];
    $ifAlias = $port['ifAlias'];
    $ifType = $port['ifType'];
    $ifOperStatus = $port['ifOperStatus'];

    if (str_i_contains($ifDescr, Config::getOsSetting($device['os'], 'good_if', Config::get('good_if')))) {
        return true;
    }

    foreach (Config::getCombined($device['os'], 'bad_if') as $bi) {
        if (str_i_contains($ifDescr, $bi)) {
            d_echo("ignored by ifDescr: $ifDescr (matched: $bi)\n");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_if_regexp') as $bir) {
        if (preg_match($bir . 'i', $ifDescr)) {
            d_echo("ignored by ifDescr: $ifDescr (matched: $bir)\n");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifname_regexp') as $bnr) {
        if (preg_match($bnr . 'i', $ifName)) {
            d_echo("ignored by ifName: $ifName (matched: $bnr)\n");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifalias_regexp') as $bar) {
        if (preg_match($bar . 'i', $ifAlias)) {
            d_echo("ignored by ifName: $ifAlias (matched: $bar)\n");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_iftype') as $bt) {
        if (Str::contains($ifType, $bt)) {
            d_echo("ignored by ifType: $ifType (matched: $bt )\n");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifoperstatus') as $bos) {
        if (Str::contains($ifOperStatus, $bos)) {
            d_echo("ignored by ifOperStatus: $ifOperStatus (matched: $bos)\n");

            return false;
        }
    }

    return true;
}

/**
 * Try to fill in data for ifDescr, ifName, and ifAlias if devices do not provide them.
 * Will not fill ifAlias if the user has overridden it
 *
 * @param array $port
 * @param array $device
 */
function port_fill_missing(&$port, $device)
{
    // When devices do not provide data, populate with other data if available
    if ($port['ifDescr'] == '' || $port['ifDescr'] == null) {
        $port['ifDescr'] = $port['ifName'];
        d_echo(' Using ifName as ifDescr');
    }
    if (! empty($device['attribs']['ifName:' . $port['ifName']])) {
        // ifAlias overridden by user, don't update it
        unset($port['ifAlias']);
        d_echo(' ifAlias overriden by user');
    } elseif ($port['ifAlias'] == '' || $port['ifAlias'] == null) {
        $port['ifAlias'] = $port['ifDescr'];
        d_echo(' Using ifDescr as ifAlias');
    }

    if ($port['ifName'] == '' || $port['ifName'] == null) {
        $port['ifName'] = $port['ifDescr'];
        d_echo(' Using ifDescr as ifName');
    }
}

function scan_new_plugins()
{
    $installed = 0; // Track how many plugins we install.

    if (file_exists(Config::get('plugin_dir'))) {
        $plugin_files = scandir(Config::get('plugin_dir'));
        foreach ($plugin_files as $name) {
            if (is_dir(Config::get('plugin_dir') . '/' . $name)) {
                if ($name != '.' && $name != '..') {
                    if (is_file(Config::get('plugin_dir') . '/' . $name . '/' . $name . '.php') && is_file(Config::get('plugin_dir') . '/' . $name . '/' . $name . '.inc.php')) {
                        $plugin_id = dbFetchRow('SELECT `plugin_id` FROM `plugins` WHERE `plugin_name` = ?', [$name]);
                        if (empty($plugin_id)) {
                            if (dbInsert(['plugin_name' => $name, 'plugin_active' => '0'], 'plugins')) {
                                $installed++;
                            }
                        }
                    }
                }
            }
        }
    }

    return $installed;
}

function scan_removed_plugins()
{
    $removed = 0; // Track how many plugins will be removed from database

    if (file_exists(Config::get('plugin_dir'))) {
        $plugin_files = scandir(Config::get('plugin_dir'));
        $installed_plugins = dbFetchColumn('SELECT `plugin_name` FROM `plugins`');
        foreach ($installed_plugins as $name) {
            if (in_array($name, $plugin_files)) {
                continue;
            }
            if (dbDelete('plugins', '`plugin_name` = ?', $name)) {
                $removed++;
            }
        }
    }

    return  $removed;
}

function validate_device_id($id)
{
    if (empty($id) || ! is_numeric($id)) {
        $return = false;
    } else {
        $device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `device_id` = ?', [$id]);
        if ($device_id == $id) {
            $return = true;
        } else {
            $return = false;
        }
    }

    return $return;
}

function convert_delay($delay)
{
    if (preg_match('/(\d+)([mhd]?)/', $delay, $matches)) {
        $multipliers = [
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
        ];

        $multiplier = $multipliers[$matches[2]] ?? 1;

        return $matches[1] * $multiplier;
    }

    return $delay === '' ? 0 : 300;
}

function normalize_snmp_ip_address($data)
{
    // $data is received from snmpwalk, can be ipv4 xxx.xxx.xxx.xxx or ipv6 xx:xx:...:xx (16 chunks)
    // ipv4 is returned unchanged, ipv6 is returned with one ':' removed out of two, like
    //  xxxx:xxxx:...:xxxx (8 chuncks)
    return preg_replace('/([0-9a-fA-F]{2}):([0-9a-fA-F]{2})/', '\1\2', explode('%', $data, 2)[0]);
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

    $tmp = rtrim($proxy, '/');
    $proxy = str_replace(['http://', 'https://'], '', $tmp);
    if (! empty($proxy)) {
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
    }
}

/**
 * Return the proxy url in guzzle format
 *
 * @return 'tcp://' + $proxy
 */
function get_guzzle_proxy()
{
    $proxy = get_proxy();

    $tmp = rtrim($proxy, '/');
    $proxy = str_replace(['http://', 'https://'], '', $tmp);

    return empty($proxy) ? '' : ('tcp://' . $proxy);
}

/**
 * Return the proxy url
 *
 * @return array|bool|false|string
 */
function get_proxy()
{
    if (getenv('http_proxy')) {
        return getenv('http_proxy');
    } elseif (getenv('https_proxy')) {
        return getenv('https_proxy');
    } elseif ($callback_proxy = Config::get('callback_proxy')) {
        return $callback_proxy;
    } elseif ($http_proxy = Config::get('http_proxy')) {
        return $http_proxy;
    }

    return false;
}

function target_to_id($target)
{
    if ($target[0] . $target[1] == 'g:') {
        $target = 'g' . dbFetchCell('SELECT id FROM device_groups WHERE name = ?', [substr($target, 2)]);
    } else {
        $target = dbFetchCell('SELECT device_id FROM devices WHERE hostname = ?', [$target]);
    }

    return $target;
}

function fix_integer_value($value)
{
    if ($value < 0) {
        $return = 4294967296 + $value;
    } else {
        $return = $value;
    }

    return $return;
}

/**
 * Find a device that has this IP. Checks ipv4_addresses and ipv6_addresses tables.
 *
 * @param string $ip
 * @return \App\Models\Device|false
 */
function device_has_ip($ip)
{
    if (IPv6::isValid($ip)) {
        $ip_address = \App\Models\Ipv6Address::query()
            ->where('ipv6_address', IPv6::parse($ip, true)->uncompressed())
            ->with('port.device')
            ->first();
    } elseif (IPv4::isValid($ip)) {
        $ip_address = \App\Models\Ipv4Address::query()
            ->where('ipv4_address', $ip)
            ->with('port.device')
            ->first();
    }

    if (isset($ip_address) && $ip_address->port) {
        return $ip_address->port->device;
    }

    return false; // not an ipv4 or ipv6 address...
}

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
    $query = 'SELECT COUNT(*) FROM `devices` WHERE `hostname`=?';
    $params = [$hostname];

    if (! empty($sysName) && ! Config::get('allow_duplicate_sysName')) {
        $query .= ' OR `sysName`=?';
        $params[] = $sysName;

        if (! empty(Config::get('mydomain'))) {
            $full_sysname = rtrim($sysName, '.') . '.' . Config::get('mydomain');
            $query .= ' OR `sysName`=?';
            $params[] = $full_sysname;
        }
    }

    return dbFetchCell($query, $params) > 0;
}

function oxidized_reload_nodes()
{
    if (Config::get('oxidized.enabled') === true && Config::get('oxidized.reload_nodes') === true && Config::has('oxidized.url')) {
        $oxidized_reload_url = Config::get('oxidized.url') . '/reload.json';
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
 * Create a new state index.  Update translations if $states is given.
 *
 * For for backward compatibility:
 *   Returns null if $states is empty, $state_name already exists, and contains state translations
 *
 * @param string $state_name the unique name for this state translation
 * @param array $states array of states, each must contain keys: descr, graph, value, generic
 * @return int|null
 */
function create_state_index($state_name, $states = [])
{
    $state_index_id = dbFetchCell('SELECT `state_index_id` FROM state_indexes WHERE state_name = ? LIMIT 1', [$state_name]);
    if (! is_numeric($state_index_id)) {
        $state_index_id = dbInsert(['state_name' => $state_name], 'state_indexes');

        // legacy code, return index so states are created
        if (empty($states)) {
            return $state_index_id;
        }
    }

    // check or synchronize states
    if (empty($states)) {
        $translations = dbFetchRows('SELECT * FROM `state_translations` WHERE `state_index_id` = ?', [$state_index_id]);
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
        $array[$state['value']] = [
            'state_index_id' => $state_index_id,
            'state_descr' => $state['descr'],
            'state_draw_graph' => $state['graph'],
            'state_value' => $state['value'],
            'state_generic_value' => $state['generic'],
        ];

        return $array;
    }, []);

    $existing_translations = dbFetchRows(
        'SELECT `state_index_id`,`state_descr`,`state_draw_graph`,`state_value`,`state_generic_value` FROM `state_translations` WHERE `state_index_id`=?',
        [$state_index_id]
    );

    foreach ($existing_translations as $translation) {
        $value = $translation['state_value'];
        if (isset($new_translations[$value])) {
            if ($new_translations[$value] != $translation) {
                dbUpdate(
                    $new_translations[$value],
                    'state_translations',
                    '`state_index_id`=? AND `state_value`=?',
                    [$state_index_id, $value]
                );
            }

            // this translation is synchronized, it doesn't need to be inserted
            unset($new_translations[$value]);
        } else {
            dbDelete('state_translations', '`state_index_id`=? AND `state_value`=?', [$state_index_id, $value]);
        }
    }

    // insert any new translations
    dbBulkInsert($new_translations, 'state_translations');
}

function create_sensor_to_state_index($device, $state_name, $index)
{
    $sensor_entry = dbFetchRow('SELECT sensor_id FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?', [
        'state',
        $device['device_id'],
        $state_name,
        $index,
    ]);
    $state_indexes_entry = dbFetchRow('SELECT state_index_id FROM `state_indexes` WHERE `state_name` = ?', [
        $state_name,
    ]);
    if (! empty($sensor_entry['sensor_id']) && ! empty($state_indexes_entry['state_index_id'])) {
        $insert = [
            'sensor_id' => $sensor_entry['sensor_id'],
            'state_index_id' => $state_indexes_entry['state_index_id'],
        ];
        foreach ($insert as $key => $val_check) {
            if (! isset($val_check)) {
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
    return '<h2>' . $message . ' Please <a href="' . Config::get('project_issues') . '">report this</a> to the ' . Config::get('project_name') . ' developers.</h2>';
}//end report_this()

function hytera_h2f($number, $nd)
{
    if (strlen(str_replace(' ', '', $number)) == 4) {
        $hex = '';
        for ($i = 0; $i < strlen($number); $i++) {
            $byte = strtoupper(dechex(ord($number[$i])));
            $byte = str_repeat('0', 2 - strlen($byte)) . $byte;
            $hex .= $byte . ' ';
        }
        $number = $hex;
        unset($hex);
    }
    $r = '';
    $y = explode(' ', $number);
    foreach ($y as $z) {
        $r = $z . '' . $r;
    }

    $hex = [];
    $number = substr($r, 0, -1);
    //$number = str_replace(" ", "", $number);
    for ($i = 0; $i < strlen($number); $i++) {
        $hex[] = substr($number, $i, 1);
    }

    $dec = [];
    $hexCount = count($hex);
    for ($i = 0; $i < $hexCount; $i++) {
        $dec[] = hexdec($hex[$i]);
    }

    $binfinal = '';
    $decCount = count($dec);
    for ($i = 0; $i < $decCount; $i++) {
        $binfinal .= sprintf('%04d', decbin($dec[$i]));
    }

    $sign = substr($binfinal, 0, 1);
    $exp = substr($binfinal, 1, 8);
    $exp = bindec($exp);
    $exp -= 127;
    $scibin = substr($binfinal, 9);
    $binint = substr($scibin, 0, $exp);
    $binpoint = substr($scibin, $exp);
    $intnumber = bindec('1' . $binint);

    $tmppoint = [];
    for ($i = 0; $i < strlen($binpoint); $i++) {
        $tmppoint[] = substr($binpoint, $i, 1);
    }

    $tmppoint = array_reverse($tmppoint);
    $tpointnumber = number_format($tmppoint[0] / 2, strlen($binpoint), '.', '');

    $pointnumber = '';
    for ($i = 1; $i < strlen($binpoint); $i++) {
        $pointnumber = number_format($tpointnumber / 2, strlen($binpoint), '.', '');
        $tpointnumber = $tmppoint[$i + 1] . substr($pointnumber, 1);
    }

    $floatfinal = $intnumber + $pointnumber;

    if ($sign == 1) {
        $floatfinal = -$floatfinal;
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
    $update = [];
    foreach ($data as $key => $value) {
        // Is the Array(DB) value different to the supplied data
        if ($entphysical[$location][$key] != $value) {
            $update[$key] = $value;
            $entphysical[$location][$key] = $value;
        } // End if
    } // end foreach

    // Do we need to update
    if (count($update) > 0) {
        dbUpdate($update, 'entPhysical', '`entPhysical_id` = ?', [$entphysical[$location]['entPhysical_id']]);
    }
    $entPhysicalId = $entphysical[$location]['entPhysical_id'];

    return [$entPhysicalId, $entPhysicalIndex];
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
            d_echo('ROOT - ' . $location . "\n");
            $shortlocation = $location;
            $parent = 0;
        } else {
            // Level 2 - No. Need to go deeper.
            d_echo('NON-ROOT - ' . $location . "\n");
            $shortlocation = array_pop($parts);
            $parentlocation = implode('/', $parts);
            d_echo('Decend - parent location: ' . $parentlocation . "\n");
            $parent = getCIMCentPhysical($parentlocation, $entphysical, $index);
        } // end if - Level 2
        d_echo('Parent: ' . $parent . "\n");

        // Now we have an ID, create the entry.
        $index++;
        $insert = [
            'device_id'                 => $device['device_id'],
            'entPhysicalIndex'          => $index,
            'entPhysicalClass'          => 'container',
            'entPhysicalVendorType'     => $location,
            'entPhysicalName'           => $shortlocation,
            'entPhysicalContainedIn'    => $parent,
            'entPhysicalParentRelPos'   => '-1',
        ];

        // Add to the DB and Array.
        $id = dbInsert($insert, 'entPhysical');
        $entphysical[$location] = dbFetchRow('SELECT * FROM entPhysical WHERE entPhysical_id=?', [$id]);

        return $index;
    } // end if - Level 1
} // end function

/* idea from https://php.net/manual/en/function.hex2bin.php comments */
function hex2bin_compat($str)
{
    if (strlen($str) % 2 !== 0) {
        trigger_error(__FUNCTION__ . '(): Hexadecimal input string must have an even length', E_USER_WARNING);
    }

    return pack('H*', $str);
}

if (! function_exists('hex2bin')) {
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

    // we need an even number of digits for hex2bin
    if (strlen($hex_data) % 2 === 1) {
        $hex_data = '0' . $hex_data;
    }

    $value = hex2bin($hex_data);
    $length = strlen($value);
    $indices = [];
    for ($i = 0; $i < $length; $i++) {
        $byte = ord($value[$i]);
        for ($j = 7; $j >= 0; $j--) {
            if ($byte & (1 << $j)) {
                $indices[] = 8 * $i + 8 - $j;
            }
        }
    }

    return $indices;
}

/**
 * Intialize global stat arrays
 */
function initStats()
{
    global $snmp_stats, $snmp_stats_last;

    if (! isset($snmp_stats)) {
        $snmp_stats = [
            'ops' => [
                'snmpget' => 0,
                'snmpgetnext' => 0,
                'snmpwalk' => 0,
            ],
            'time' => [
                'snmpget' => 0.0,
                'snmpgetnext' => 0.0,
                'snmpwalk' => 0.0,
            ],
        ];
        $snmp_stats_last = $snmp_stats;
    }
}

/**
 * Print out the stats totals since the last time this function was called
 *
 * @param bool $update_only Only update the stats checkpoint, don't print them
 */
function printChangedStats($update_only = false)
{
    global $snmp_stats, $db_stats;
    global $snmp_stats_last, $db_stats_last;
    $output = sprintf(
        '>> SNMP: [%d/%.2fs] MySQL: [%d/%.2fs]',
        array_sum($snmp_stats['ops'] ?? []) - array_sum($snmp_stats_last['ops'] ?? []),
        array_sum($snmp_stats['time'] ?? []) - array_sum($snmp_stats_last['time'] ?? []),
        array_sum($db_stats['ops'] ?? []) - array_sum($db_stats_last['ops'] ?? []),
        array_sum($db_stats['time'] ?? []) - array_sum($db_stats_last['time'] ?? [])
    );

    foreach (app('Datastore')->getStats() as $datastore => $stats) {
        /** @var \LibreNMS\Data\Measure\MeasurementCollection $stats */
        $output .= sprintf(' %s: [%d/%.2fs]', $datastore, $stats->getCountDiff(), $stats->getDurationDiff());
        $stats->checkpoint();
    }

    if (! $update_only) {
        echo $output . PHP_EOL;
    }

    // make a new checkpoint
    $snmp_stats_last = $snmp_stats;
    $db_stats_last = $db_stats;
}

/**
 * Print global stat arrays
 */
function printStats()
{
    global $snmp_stats, $db_stats;

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

    foreach (app('Datastore')->getStats() as $datastore => $stats) {
        /** @var \LibreNMS\Data\Measure\MeasurementCollection $stats */
        printf('%s [%d/%.2fs]:', $datastore, $stats->getTotalCount(), $stats->getTotalDuration());

        foreach ($stats as $stat) {
            /** @var \LibreNMS\Data\Measure\MeasurementSummary $stat */
            printf(' %s[%d/%.2fs]', ucfirst($stat->getType()), $stat->getCount(), $stat->getDuration());
        }
        echo PHP_EOL;
    }
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
    $poller_target = Device::pollerTarget($device['hostname']);
    $ping_response = isPingable($poller_target, $address_family, $device['attribs']);
    $device_perf = $ping_response['db'];
    $device_perf['device_id'] = $device['device_id'];
    $device_perf['timestamp'] = ['NOW()'];
    $maintenance = DeviceCache::get($device['device_id'])->isUnderMaintenance();
    $consider_maintenance = Config::get('graphing.availability_consider_maintenance');
    $state_update_again = false;

    if ($record_perf === true && can_ping_device($device['attribs'])) {
        $trace_debug = [];
        if ($ping_response['result'] === false && Config::get('debug.run_trace', false)) {
            $trace_debug = runTraceroute($device);
        }
        $device_perf['debug'] = json_encode($trace_debug);
        dbInsert($device_perf, 'device_perf');

        // if device_perf is inserted and the ping was successful then update device last_ping timestamp
        if (! empty($ping_response['last_ping_timetaken']) && $ping_response['last_ping_timetaken'] != '0') {
            dbUpdate(
                ['last_ping' => NOW(), 'last_ping_timetaken' => $ping_response['last_ping_timetaken']],
                'devices',
                'device_id=?',
                [$device['device_id']]
            );
        }
    }
    $response = [];
    $response['ping_time'] = $ping_response['last_ping_timetaken'];
    if ($ping_response['result']) {
        if ($device['snmp_disable'] || isSNMPable($device)) {
            $response['status'] = '1';
            $response['status_reason'] = '';
        } else {
            echo 'SNMP Unreachable';
            $response['status'] = '0';
            $response['status_reason'] = 'snmp';
        }
    } else {
        echo 'Unpingable';
        $response['status'] = '0';
        $response['status_reason'] = 'icmp';
    }

    // Special case where the device is still down, optional mode is on, device not in maintenance mode and has no ongoing outages
    if (($consider_maintenance && ! $maintenance) && ($device['status'] == '0' && $response['status'] == '0')) {
        $state_update_again = empty(dbFetchCell('SELECT going_down FROM device_outages WHERE device_id=? AND up_again IS NULL ORDER BY going_down DESC', [$device['device_id']]));
    }

    if ($device['status'] != $response['status'] || $device['status_reason'] != $response['status_reason'] || $state_update_again) {
        if (! $state_update_again) {
            dbUpdate(
                ['status' => $response['status'], 'status_reason' => $response['status_reason']],
                'devices',
                'device_id=?',
                [$device['device_id']]
            );
        }

        if ($response['status']) {
            $type = 'up';
            $reason = $device['status_reason'];

            $going_down = dbFetchCell('SELECT going_down FROM device_outages WHERE device_id=? AND up_again IS NULL ORDER BY going_down DESC', [$device['device_id']]);
            if (! empty($going_down)) {
                $up_again = time();
                dbUpdate(
                    ['device_id' => $device['device_id'], 'up_again' => $up_again],
                    'device_outages',
                    'device_id=? and going_down=? and up_again is NULL',
                    [$device['device_id'], $going_down]
                );
            }
        } else {
            $type = 'down';
            $reason = $response['status_reason'];

            if ($device['status'] != $response['status']) {
                if (! $consider_maintenance || (! $maintenance && $consider_maintenance)) {
                    // use current time as a starting point when an outage starts
                    $data = ['device_id' => $device['device_id'],
                        'going_down' => time(), ];
                    dbInsert($data, 'device_outages');
                }
            }
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
        dbUpdate(['icon' => $icon], 'devices', 'device_id=?', [$device['device_id']]);
        echo "Changed Icon! : $icon\n";
    }
}

/**
 * Function to generate Mac OUI Cache
 */
function cache_mac_oui()
{
    // timers:
    $mac_oui_refresh_int_min = 86400 * rand(7, 11); // 7 days + a random number between 0 and 4 days
    $mac_oui_cache_time = 1296000; // we keep data during 15 days maximum

    $lock = Cache::lock('macouidb-refresh', $mac_oui_refresh_int_min); //We want to refresh after at least $mac_oui_refresh_int_min

    if (Config::get('mac_oui.enabled') !== true) {
        echo 'Mac OUI integration disabled' . PHP_EOL;

        return 0;
    }

    if ($lock->get()) {
        echo 'Caching Mac OUI' . PHP_EOL;
        try {
            $mac_oui_url = 'https://macaddress.io/database/macaddress.io-db.json';
            echo '  -> Downloading ...' . PHP_EOL;
            $get = Requests::get($mac_oui_url, [], ['proxy' => get_proxy()]);
            echo '  -> Processing ...' . PHP_EOL;
            $json_data = $get->body;
            foreach (explode("\n", $json_data) as $json_line) {
                $entry = json_decode($json_line);
                if ($entry && $entry->{'assignmentBlockSize'} == 'MA-L') {
                    $oui = strtolower(str_replace(':', '', $entry->{'oui'}));
                    $key = 'OUIDB-' . $oui;
                    Cache::put($key, $entry->{'companyName'}, $mac_oui_cache_time);
                }
            }
        } catch (Exception $e) {
            echo 'Error processing Mac OUI :' . PHP_EOL;
            echo 'Exception: ' . get_class($e) . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;

            $lock->release(); // we did not succeed so we'll try again next time

            return 1;
        }
    }

    return 0;
}

/**
 * Function to generate PeeringDB Cache
 */
function cache_peeringdb()
{
    if (Config::get('peeringdb.enabled') === true) {
        $peeringdb_url = 'https://peeringdb.com/api';
        // We cache for 71 hours
        $cached = dbFetchCell('SELECT count(*) FROM `pdb_ix` WHERE (UNIX_TIMESTAMP() - timestamp) < 255600');
        if ($cached == 0) {
            $rand = rand(3, 30);
            echo "No cached PeeringDB data found, sleeping for $rand seconds" . PHP_EOL;
            sleep($rand);
            $peer_keep = [];
            $ix_keep = [];
            foreach (dbFetchRows('SELECT `bgpLocalAs` FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 GROUP BY `bgpLocalAs`') as $as) {
                $asn = $as['bgpLocalAs'];
                $get = Requests::get($peeringdb_url . '/net?depth=2&asn=' . $asn, [], ['proxy' => get_proxy()]);
                $json_data = $get->body;
                $data = json_decode($json_data);
                $ixs = $data->{'data'}[0]->{'netixlan_set'};
                foreach ($ixs as $ix) {
                    $ixid = $ix->{'ix_id'};
                    $tmp_ix = dbFetchRow('SELECT * FROM `pdb_ix` WHERE `ix_id` = ? AND asn = ?', [$ixid, $asn]);
                    if ($tmp_ix) {
                        $pdb_ix_id = $tmp_ix['pdb_ix_id'];
                        $update = ['name' => $ix->{'name'}, 'timestamp' => time()];
                        dbUpdate($update, 'pdb_ix', '`ix_id` = ? AND `asn` = ?', [$ixid, $asn]);
                    } else {
                        $insert = [
                            'ix_id' => $ixid,
                            'name' => $ix->{'name'},
                            'asn' => $asn,
                            'timestamp' => time(),
                        ];
                        $pdb_ix_id = dbInsert($insert, 'pdb_ix');
                    }
                    $ix_keep[] = $pdb_ix_id;
                    $get_ix = Requests::get("$peeringdb_url/netixlan?ix_id=$ixid", [], ['proxy' => get_proxy()]);
                    $ix_json = $get_ix->body;
                    $ix_data = json_decode($ix_json);
                    $peers = $ix_data->{'data'};
                    foreach ($peers as $index => $peer) {
                        $peer_name = get_astext($peer->{'asn'});
                        $tmp_peer = dbFetchRow('SELECT * FROM `pdb_ix_peers` WHERE `peer_id` = ? AND `ix_id` = ?', [$peer->{'id'}, $ixid]);
                        if ($tmp_peer) {
                            $peer_keep[] = $tmp_peer['pdb_ix_peers_id'];
                            $update = [
                                'remote_asn'     => $peer->{'asn'},
                                'remote_ipaddr4'  => $peer->{'ipaddr4'},
                                'remote_ipaddr6' => $peer->{'ipaddr6'},
                                'name'           => $peer_name,
                            ];
                            dbUpdate($update, 'pdb_ix_peers', '`pdb_ix_peers_id` = ?', [$tmp_peer['pdb_ix_peers_id']]);
                        } else {
                            $peer_insert = [
                                'ix_id'          => $ixid,
                                'peer_id'        => $peer->{'id'},
                                'remote_asn'     => $peer->{'asn'},
                                'remote_ipaddr4' => $peer->{'ipaddr4'},
                                'remote_ipaddr6' => $peer->{'ipaddr6'},
                                'name'           => $peer_name,
                                'timestamp'      => time(),
                            ];
                            $peer_keep[] = dbInsert($peer_insert, 'pdb_ix_peers');
                        }
                    }
                }
            }

            // cleanup
            if (empty($peer_keep)) {
                dbDelete('pdb_ix_peers');
            } else {
                dbDelete('pdb_ix_peers', '`pdb_ix_peers_id` NOT IN ' . dbGenPlaceholders(count($peer_keep)), $peer_keep);
            }
            if (empty($ix_keep)) {
                dbDelete('pdb_ix');
            } else {
                dbDelete('pdb_ix', '`pdb_ix_id` NOT IN ' . dbGenPlaceholders(count($ix_keep)), $ix_keep);
            }
        } else {
            echo 'Cached PeeringDB data found.....' . PHP_EOL;
        }
    } else {
        echo 'Peering DB integration disabled' . PHP_EOL;
    }
}

/**
 * Get an array of the schema files.
 * schema_version => full_file_name
 *
 * @return mixed
 */
function get_schema_list()
{
    // glob returns an array sorted by filename
    $files = glob(Config::get('install_dir') . '/sql-schema/*.sql');

    // set the keys to the db schema version
    $files = array_reduce($files, function ($array, $file) {
        $array[(int) basename($file, '.sql')] = $file;

        return $array;
    }, []);

    ksort($files); // fix dbSchema 1000 order

    return $files;
}

/**
 * @param $device
 * @return int|null
 */
function get_device_oid_limit($device)
{
    // device takes priority
    if (! empty($device['attribs']['snmp_max_oid'])) {
        return $device['attribs']['snmp_max_oid'];
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
 * If Distributed, create a lock, then purge the mysql table
 *
 * @param string $table
 * @param string $sql
 * @return int exit code
 */
function lock_and_purge($table, $sql)
{
    $purge_name = $table . '_purge';
    $lock = Cache::lock($purge_name, 86000);
    if ($lock->get()) {
        $purge_days = Config::get($purge_name);

        $name = str_replace('_', ' ', ucfirst($table));
        if (is_numeric($purge_days)) {
            if (dbDelete($table, $sql, [$purge_days])) {
                echo "$name cleared for entries over $purge_days days\n";
            }
        }
        $lock->release();

        return 0;
    }

    return -1;
}

/**
 * If Distributed, create a lock, then purge the mysql table according to the sql query
 *
 * @param string $table
 * @param string $sql
 * @param string $msg
 * @return int exit code
 */
function lock_and_purge_query($table, $sql, $msg)
{
    $purge_name = $table . '_purge';

    $purge_duration = Config::get($purge_name);
    if (! (is_numeric($purge_duration) && $purge_duration > 0)) {
        return -2;
    }
    $lock = Cache::lock($purge_name, 86000);
    if ($lock->get()) {
        if (dbQuery($sql, [$purge_duration])) {
            printf($msg, $purge_duration);
        }
        $lock->release();

        return 0;
    }

    return -1;
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
        if (preg_match($bir . 'i', $disk['diskIODevice'])) {
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
    $msg = str_replace('%', '', $msg);
    $postdata = ['user' => $username, 'msg' => $msg];
    $oxidized_url = Config::get('oxidized.url');
    if (! empty($oxidized_url)) {
        Requests::put("$oxidized_url/node/next/$hostname", [], json_encode($postdata), ['proxy' => get_proxy()]);

        return true;
    }

    return false;
}//end oxidized_node_update()

/**
 * @params int code
 * @params int subcode
 * @return string
 * Take a BGP error code and subcode to return a string representation of it
 */
function describe_bgp_error_code($code, $subcode)
{
    // https://www.iana.org/assignments/bgp-parameters/bgp-parameters.xhtml#bgp-parameters-3

    $message = 'Unknown';

    $error_code_key = 'bgp.error_codes.' . $code;
    $error_subcode_key = 'bgp.error_subcodes.' . $code . '.' . $subcode;

    $error_code_message = __($error_code_key);
    $error_subcode_message = __($error_subcode_key);

    if ($error_subcode_message != $error_subcode_key) {
        $message = $error_code_message . ' - ' . $error_subcode_message;
    } elseif ($error_code_message != $error_code_key) {
        $message = $error_code_message;
    }

    return $message;
}
