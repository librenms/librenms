<?php

use LibreNMS\Config;
use LibreNMS\Util\Rewrite;

function rewrite_location($location)
{
    return \LibreNMS\Util\Rewrite::location($location);
}

function formatMac($mac)
{
    return \LibreNMS\Util\Rewrite::readableMac($mac);
}

function rewrite_entity_descr($descr)
{
    $descr = str_replace('Distributed Forwarding Card', 'DFC', $descr);
    $descr = preg_replace('/7600 Series SPA Interface Processor-/', '7600 SIP-', $descr);
    $descr = preg_replace('/Rev\.\ [0-9\.]+\ /', '', $descr);
    $descr = preg_replace('/12000 Series Performance Route Processor/', '12000 PRP', $descr);
    $descr = preg_replace('/^12000/', '', $descr);
    $descr = preg_replace('/Gigabit Ethernet/', 'GigE', $descr);
    $descr = preg_replace('/^ASR1000\ /', '', $descr);
    $descr = str_replace('Routing Processor', 'RP', $descr);
    $descr = str_replace('Route Processor', 'RP', $descr);
    $descr = str_replace('Switching Processor', 'SP', $descr);
    $descr = str_replace('Sub-Module', 'Module ', $descr);
    $descr = str_replace('DFC Card', 'DFC', $descr);
    $descr = str_replace('Centralized Forwarding Card', 'CFC', $descr);
    $descr = str_replace('Power Supply Module', 'PSU ', $descr);
    $descr = str_replace('/Voltage Sensor/', 'Voltage', $descr);
    $descr = str_replace('Sensor', '', $descr);
    $descr = str_replace('PMOD', 'PSU', $descr);
    $descr = preg_replace('/^temperatures /', '', $descr);
    $descr = preg_replace('/^voltages /', '', $descr);
    $descr = str_replace('PowerSupply', 'PSU ', $descr);

    return $descr;
}

/**
 * Clean port values for html display
 * Add label to the port array (usually one of ifAlias, ifName, ifDescr)
 *
 * @param array $interface
 * @param null|array $device
 * @return mixed
 */
function cleanPort($interface, $device = null)
{
    $interface['ifAlias'] = display($interface['ifAlias']);
    $interface['ifName'] = display($interface['ifName']);
    $interface['ifDescr'] = display($interface['ifDescr']);

    if (! $device) {
        $device = device_by_id_cache($interface['device_id']);
    }

    $os = strtolower($device['os']);

    if (Config::get("os.$os.ifname")) {
        $interface['label'] = $interface['ifName'];

        if ($interface['ifName'] == '') {
            $interface['label'] = $interface['ifDescr'];
        }
    } elseif (Config::get("os.$os.ifalias")) {
        $interface['label'] = $interface['ifAlias'];
    } else {
        $interface['label'] = $interface['ifDescr'];
        if (Config::get("os.$os.ifindex")) {
            $interface['label'] = $interface['label'] . ' ' . $interface['ifIndex'];
        }
    }

    if ($device['os'] == 'speedtouch') {
        [$interface['label']] = explode('thomson', $interface['label']);
    }

    if (is_array(Config::get('rewrite_if'))) {
        foreach (Config::get('rewrite_if') as $src => $val) {
            if (stristr($interface['label'], $src)) {
                $interface['label'] = $val;
            }
        }
    }

    if (is_array(Config::get('rewrite_if_regexp'))) {
        foreach (Config::get('rewrite_if_regexp') as $reg => $val) {
            if (preg_match($reg . 'i', $interface['label'])) {
                $interface['label'] = preg_replace($reg . 'i', $val, $interface['label']);
            }
        }
    }

    return $interface;
}

function translate_ifOperStatus($ifOperStatus)
{
    $translate_ifOperStatus = [
        '1' => 'up',
        '2' => 'down',
        '3' => 'testing',
        '4' => 'unknown',
        '5' => 'dormant',
        '6' => 'notPresent',
        '7' => 'lowerLayerDown',
    ];

    if (isset($translate_ifOperStatus[$ifOperStatus])) {
        $ifOperStatus = $translate_ifOperStatus[$ifOperStatus];
    }

    return $ifOperStatus;
}

function translate_ifAdminStatus($ifAdminStatus)
{
    $translate_ifAdminStatus = [
        '1' => 'up',
        '2' => 'down',
        '3' => 'testing',
    ];

    if (isset($translate_ifAdminStatus[$ifAdminStatus])) {
        $ifAdminStatus = $translate_ifAdminStatus[$ifAdminStatus];
    }

    return $ifAdminStatus;
}

// Specific rewrite functions

function makeshortif($if)
{
    $rewrite_shortif = [
        'tengigabitethernet'  => 'Te',
        'ten-gigabitethernet' => 'Te',
        'tengige'             => 'Te',
        'gigabitethernet'     => 'Gi',
        'fastethernet'        => 'Fa',
        'ethernet'            => 'Et',
        'serial'              => 'Se',
        'pos'                 => 'Pos',
        'port-channel'        => 'Po',
        'atm'                 => 'Atm',
        'null'                => 'Null',
        'loopback'            => 'Lo',
        'dialer'              => 'Di',
        'vlan'                => 'Vlan',
        'tunnel'              => 'Tunnel',
        'serviceinstance'     => 'SI',
        'dwdm'                => 'DWDM',
        'bundle-ether'        => 'BE',
    ];

    $if = fixifName($if);
    $if = strtolower($if);
    $if = array_str_replace($rewrite_shortif, $if);

    return $if;
}

function rewrite_ios_features($features)
{
    $rewrite_ios_features = [
        'PK9S'                => 'IP w/SSH LAN Only',
        'LANBASEK9'           => 'Lan Base Crypto',
        'LANBASE'             => 'Lan Base',
        'ADVENTERPRISEK9_IVS' => 'Advanced Enterprise Crypto Voice',
        'ADVENTERPRISEK9'     => 'Advanced Enterprise Crypto',
        'ADVSECURITYK9'       => 'Advanced Security Crypto',
        'K91P'                => 'Provider Crypto',
        'K4P'                 => 'Provider Crypto',
        'ADVIPSERVICESK9'     => 'Adv IP Services Crypto',
        'ADVIPSERVICES'       => 'Adv IP Services',
        'IK9P'                => 'IP Plus Crypto',
        'K9O3SY7'             => 'IP ADSL FW IDS Plus IPSEC 3DES',
        'SPSERVICESK9'        => 'SP Services Crypto',
        'PK9SV'               => 'IP MPLS/IPV6 W/SSH + BGP',
        'IS'                  => 'IP Plus',
        'IPSERVICESK9'        => 'IP Services Crypto',
        'BROADBAND'           => 'Broadband',
        'IPBASE'              => 'IP Base',
        'IPSERVICE'           => 'IP Services',
        'P'                   => 'Service Provider',
        'P11'                 => 'Broadband Router',
        'G4P5'                => 'NRP',
        'JK9S'                => 'Enterprise Plus Crypto',
        'IK9S'                => 'IP Plus Crypto',
        'JK'                  => 'Enterprise Plus',
        'I6Q4L2'              => 'Layer 2',
        'I6K2L2Q4'            => 'Layer 2 Crypto',
        'C3H2S'               => 'Layer 2 SI/EI',
        '_WAN'                => ' + WAN',
    ];

    $type = array_preg_replace($rewrite_ios_features, $features);

    return $features;
}

function rewrite_junose_hardware($hardware)
{
    $rewrite_junose_hardware = [
        'juniErx1400' => 'ERX-1400',
        'juniErx700'  => 'ERX-700',
        'juniErx1440' => 'ERX-1440',
        'juniErx705'  => 'ERX-705',
        'juniErx310'  => 'ERX-310',
        'juniE320'    => 'E320',
        'juniE120'    => 'E120',
        'juniSsx1400' => 'SSX-1400',
        'juniSsx700'  => 'SSX-700',
        'juniSsx1440' => 'SSX-1440',
    ];

    $hardware = array_str_replace($rewrite_junose_hardware, $hardware);

    return $hardware;
}

function rewrite_generic_hardware($hardware)
{
    $rewrite_GenericHW = [
        ' Computer Corporation' => '',
        ' Corporation'          => '',
        ' Inc.'                 => '',
    ];

    return array_str_replace($rewrite_GenericHW, $hardware);
}

function fixiftype($type)
{
    return Rewrite::normalizeIfType($type);
}

function fixifName($inf)
{
    return Rewrite::normalizeIfName($inf);
}

function short_hrDeviceDescr($dev)
{
    $rewrite_hrDevice = [
        'GenuineIntel:' => '',
        'AuthenticAMD:' => '',
        'Intel(R)'      => '',
        'CPU'           => '',
        '(R)'           => '',
        '  '            => ' ',
    ];

    $dev = array_str_replace($rewrite_hrDevice, $dev);
    $dev = preg_replace('/\ +/', ' ', $dev);
    $dev = trim($dev);

    return $dev;
}

function short_port_descr($desc)
{
    [$desc] = explode('(', $desc);
    [$desc] = explode('[', $desc);
    [$desc] = explode('{', $desc);
    [$desc] = explode('|', $desc);
    [$desc] = explode('<', $desc);
    $desc = trim($desc);

    return $desc;
}

// Underlying rewrite functions
function array_str_replace($array, $string)
{
    foreach ($array as $search => $replace) {
        $string = str_replace($search, $replace, $string);
    }

    return $string;
}

function array_preg_replace($array, $string)
{
    foreach ($array as $search => $replace) {
        $string = preg_replace($search, $replace, $string);
    }

    return $string;
}

function rewrite_adslLineType($adslLineType)
{
    $adslLineTypes = [
        'noChannel'          => 'No Channel',
        'fastOnly'           => 'Fastpath',
        'interleavedOnly'    => 'Interleaved',
        'fastOrInterleaved'  => 'Fast/Interleaved',
        'fastAndInterleaved' => 'Fast+Interleaved',
    ];

    foreach ($adslLineTypes as $type => $text) {
        if ($adslLineType == $type) {
            $adslLineType = $text;
        }
    }

    return $adslLineType;
}

function ipmiSensorName($hardwareId, $sensorIpmi)
{
    $ipmiSensorsNames = [
        'HP ProLiant BL460c G6' => [
            'Temp 1' => 'Ambient zone',
            'Temp 2' => 'CPU 1',
            'Temp 3' => 'CPU 2',
            'Temp 4' => 'Memory zone',
            'Temp 5' => 'Memory zone',
            'Temp 6' => 'Memory zone',
            'Temp 7' => 'System zone',
            'Temp 8' => 'System zone',
            'Temp 9' => 'System zone',
            'Temp 10' => 'Storage zone',
            'Power Meter' => 'Power usage',
        ],
        'HP ProLiant BL460c G1' => [
            'Temp 1' => 'System zone',
            'Temp 2' => 'CPU 1 zone',
            'Temp 3' => 'CPU 1',
            'Temp 4' => 'CPU 1',
            'Temp 5' => 'CPU 2 zone',
            'Temp 6' => 'CPU 2',
            'Temp 7' => 'CPU 2',
            'Temp 8' => 'Memory zone',
            'Temp 9' => 'Ambient zone',
            'Power Meter' => 'Power usage',
        ],
    ];

    if (isset($ipmiSensorsNames[$hardwareId], $ipmiSensorsNames[$hardwareId][$sensorIpmi])) {
        return $ipmiSensorsNames[$hardwareId][$sensorIpmi];
    }

    return $sensorIpmi;
}

/**
 * @param $descr
 * @return int
 */
function get_nagios_state($descr)
{
    switch ($descr) {
        case 'On':
        case 'Okay':
        case 'Ok':
            return 0;
            break;
        case 'Standby':
        case 'Idle':
        case 'Maintenance':
            return 1;
            break;
        case 'Under':
        case 'Over':
            return 2;
            break;
        default:
            return 3;
            break;
    }
}

/**
 * @param $state
 * @return int
 */
function apc_relay_state($state)
{
    switch ($state) {
        case 'immediateCloseEMS':
        case 'immediateOnEMS':
            return 1;
            break;
        case 'immediateOpenEMS':
        case 'immediateOffEMS':
            return 2;
            break;
    }
}

/**
 * @param $value
 * @return mixed
 */
function return_number($value)
{
    preg_match('/[\d\.\-]+/', $value, $temp_response);
    if (! empty($temp_response[0])) {
        $value = $temp_response[0];
    }

    return $value;
}

function parse_entity_state($state, $value)
{
    $data = [
        'entStateOper' => [
            1 => ['text' => 'unavailable', 'color' => 'default'],
            2 => ['text' => 'disabled', 'color' => 'danger'],
            3 => ['text' => 'enabled', 'color' => 'success'],
            4 => ['text' => 'testing', 'color' => 'warning'],
        ],
        'entStateUsage' => [
            1 => ['text' => 'unavailable', 'color' => 'default'],
            2 => ['text' => 'idle', 'color' => 'info'],
            3 => ['text' => 'active', 'color' => 'success'],
            4 => ['text' => 'busy', 'color' => 'success'],
        ],
        'entStateStandby' => [
            1 => ['text' => 'unavailable', 'color' => 'default'],
            2 => ['text' => 'hotStandby', 'color' => 'info'],
            3 => ['text' => 'coldStandby', 'color' => 'info'],
            4 => ['text' => 'providingService', 'color' => 'success'],
        ],
        'entStateAdmin' => [
            1 => ['text' => 'unknown', 'color' => 'default'],
            2 => ['text' => 'locked', 'color' => 'info'],
            3 => ['text' => 'shuttingDown', 'color' => 'warning'],
            4 => ['text' => 'unlocked', 'color' => 'success'],
        ],
    ];

    if (isset($data[$state][$value])) {
        return $data[$state][$value];
    }

    return ['text'=>'na', 'color'=>'default'];
}

function parse_entity_state_alarm($bits)
{
    // not sure if this is correct
    $data = [
        0 => ['text' => 'unavailable', 'color' => 'default'],
        1 => ['text' => 'underRepair', 'color' => 'warning'],
        2 => ['text' => 'critical', 'color' => 'danger'],
        3 => ['text' => 'major', 'color' => 'danger'],
        4 => ['text' => 'minor', 'color' => 'info'],
        5 => ['text' => 'warning', 'color' => 'warning'],
        6 => ['text' => 'indeterminate', 'color' => 'default'],
    ];

    $alarms = str_split(base_convert($bits, 16, 2));
    $active_alarms = array_filter($alarms);

    return array_intersect_key($data, $active_alarms);
}
