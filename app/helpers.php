<?php
/**
 * helpers.php
 *
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
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 *
 *
 * Order of Contents
 * - Common
 *  - c_echo()
 *  - d_echo()
 *  - is_pingable()
 *  - fping()
 *  - can_ping_device()
 *  - set_numeric()
 *  - starts_with()
 *  - ends_with()
 *  - compare_var()
 *  - preg_match_any()
 * - Validation
 *  - ip_exists
 *  - host_exists
 *  - snmpTransportToAddressFamily()
 *
 */
use Librenms\Config;

/**
 * Output using console color if possible
 * https://github.com/pear/Console_Color2/blob/master/examples/documentation
 *
 * @param string $string the string to print with console color
 * @param bool $enabled if set to false, this function does nothing
 */
function c_echo($string, $enabled = true)
{
    if (!$enabled) {
        return;
    }

    if (isCli()) {
        global $console_color;
        if ($console_color) {
            echo $console_color->convert($string);
        } else {
            // limited functionality for validate.php
            $search = array(
                '/%n/',
                '/%g/',
                '/%R/',
                '/%Y/',
                '/%B/',
                '/%((%)|.)/' // anything left over replace with empty string
            );
            $replace = array(
                "\e[0m",
                "\e[32m",
                "\e[1;31m",
                "\e[1;33m",
                "\e[1;34m",
                ""
            );
            echo preg_replace($search, $replace, $string);
        }
    } else {
        echo preg_replace('/%((%)|.)/', '', $string);
    }
}

/*
 * convenience function - please use this instead of 'if ($debug) { echo ...; }'
 */
function d_echo($text, $no_debug_text = null)
{
    global $debug, $php_debug;
    if ($debug) {
        if (isset($php_debug)) {
            $php_debug[] = $text;
        } else {
            print_r($text);
        }
    } elseif ($no_debug_text) {
        echo "$no_debug_text";
    }
} // d_echo

/**
 * Check if the given host responds to ICMP echo requests ("pings").
 *
 * @param string $hostname The hostname or IP address to send ping requests to.
 * @param int $address_family The address family (AF_INET for IPv4 or AF_INET6 for IPv6) to use. Defaults to IPv4. Will *not* be autodetected for IP addresses, so it has to be set to AF_INET6 when pinging an IPv6 address or an IPv6-only host.
 * @param array $attribs The device attributes
 *
 * @return array  'result' => bool pingable, 'last_ping_timetaken' => int time for last ping, 'db' => fping results
 */
function isPingable($hostname, $address_family = AF_INET, $attribs = array())
{
    $config = Config::get('fping_options');

    $response = array();
    // if (can_ping_device($attribs) === true) {
    if (Config::get('icmp_check')) {
        $fping_params = '';
        if (is_numeric($config['timeout'])) {
            if ($config['timeout'] < 50) {
                $config['timeout'] = 50;
            }
            if ($config['interval'] < $config['timeout']) {
                $config['interval'] = $config['timeout'];
            }
            $fping_params .= ' -t ' . $config['timeout'];
        }
        if (is_numeric($config['count']) && $config['count'] > 0) {
            $fping_params .= ' -c ' . $config['count'];
        }
        if (is_numeric($config['interval'])) {
            if ($config['interval'] < 20) {
                $config['interval'] = 20;
            }
            $fping_params .= ' -p ' . $config['interval'];
        }
        $status = fping($hostname, $fping_params, $address_family);
        if ($status['exitcode'] > 0 || $status['loss'] == 100) {
            $response['result'] = false;
        } else {
            $response['result'] = true;
        }
        if (is_numeric($status['avg'])) {
            $response['last_ping_timetaken'] = $status['avg'];
        }
        $response['db'] = array_intersect_key($status, array_flip(array('xmt','rcv','loss','min','max','avg')));
    } else {
        $response['result'] = true;
        $response['last_ping_timetaken'] = 0;
    }
    return($response);
}

function fping($host, $params, $address_family = AF_INET)
{
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    // Default to AF_INET (IPv4)
    $fping_path = Config::get('fping');
    if ($address_family == AF_INET6) {
        $fping_path = Config::get('fping6');
    }

    $process = proc_open($fping_path . ' -e -q ' .$params . ' ' .$host.' 2>&1', $descriptorspec, $pipes);
    $read = '';

    $proc_status = 0;
    if (is_resource($process)) {
        fclose($pipes[0]);

        while (!feof($pipes[1])) {
            $read .= fgets($pipes[1], 1024);
        }
        fclose($pipes[1]);
        $proc_status = proc_get_status($process);
        proc_close($process);
    }

    preg_match('/[0-9]+\/[0-9]+\/[0-9]+%/', $read, $loss_tmp);
    preg_match('/[0-9\.]+\/[0-9\.]+\/[0-9\.]*$/', $read, $latency);
    $loss = preg_replace("/%/", "", $loss_tmp[0]);
    list($xmt,$rcv,$loss) = preg_split("/\//", $loss);
    list($min,$avg,$max) = preg_split("/\//", $latency[0]);
    if ($loss < 0) {
        $xmt = 1;
        $rcv = 1;
        $loss = 100;
    }
    $xmt      = set_numeric($xmt);
    $rcv      = set_numeric($rcv);
    $loss     = set_numeric($loss);
    $min      = set_numeric($min);
    $max      = set_numeric($max);
    $avg      = set_numeric($avg);
    $response = array('xmt'=>$xmt,'rcv'=>$rcv,'loss'=>$loss,'min'=>$min,'max'=>$max,'avg'=>$avg,'exitcode'=>$proc_status['exitcode']);
    return $response;
}

/**
 * Checks if config allows us to ping this device
 * $attribs contains an array of all of this devices
 * attributes
 * @param array $attribs Device attributes
 * @return bool
**/
function can_ping_device($attribs)
{
    if (Config::get('icmp_check') === true && !(isset($attribs['override_icmp_disable']) && $attribs['override_icmp_disable'] != "true")) {
        return true;
    } else {
        return false;
    }
} // end can_ping_device

/*
 * @param $value
 * @param int $default
 * @return int
 */
function set_numeric($value, $default = 0)
{
    if (is_nan($value) ||
        is_infinite($value) ||
        !isset($value) ||
        !is_numeric($value)
    ) {
        $value = $default;
    }
    return $value;
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle))) {
                return true;
            }
        }
        return false;
    }
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
// ---- VALIDATION

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
    $query = \App\Models\Device::where('hostname', $hostname);

    if (!empty($sysName) && !Config::get('allow_duplicate_sysName')) {
        $query->orWhere('sysName', $sysName);
        if (!empty(Config::get('mydomain'))) {
            $query->orWhere('sysName', rtrim($sysName, '.') . '.' . Config::get('mydomain'));
        }
    }
    return $query->count() > 0;
}

function ip_exists($ip)
{
    // Function to check if an IP exists in the DB already
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
        $result = \App\Models\IPv6Address::where('ipv6_address', $ip)->where('ipv6_compressed', $ip)->get()->count();
        // $dbresult = dbFetchRow("SELECT `ipv6_address_id` FROM `ipv6_addresses` WHERE `ipv6_address` = ? OR `ipv6_compressed` = ?", array($ip, $ip));
        return $result > 0;
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
        $result = \App\Models\IPv4Address::where('ipv4_address', $ip)->get()->count();
        // $dbresult = dbFetchRow("SELECT `ipv4_address_id` FROM `ipv4_addresses` WHERE `ipv4_address` = ?", array($ip));
        return $result > 0;
    }

    // not an ipv4 or ipv6 address...
    return false;
}

/**
 * Try to determine the address family (IPv4 or IPv6) associated with an SNMP
 * transport specifier (like "udp", "udp6", etc.).
 *
 * @param string $transport The SNMP transport specifier, for example "udp",
 *                          "udp6", "tcp", or "tcp6". See `man snmpcmd`,
 *                          section "Agent Specification" for a full list.
 *
 * @return int The address family associated with the given transport
 *             specifier: AF_INET for IPv4 (or local connections not associated
 *             with an IP stack), AF_INET6 for IPv6.
 */
function snmpTransportToAddressFamily($transport)
{
    if (!isset($transport)) {
        $transport = 'udp';
    }

    $ipv6_snmp_transport_specifiers = array('udp6', 'udpv6', 'udpipv6', 'tcp6', 'tcpv6', 'tcpipv6');

    if (in_array($transport, $ipv6_snmp_transport_specifiers)) {
        return AF_INET6;
    } else {
        return AF_INET;
    }
}
