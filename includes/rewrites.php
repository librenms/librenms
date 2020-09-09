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
    $interface['ifName']  = display($interface['ifName']);
    $interface['ifDescr'] = display($interface['ifDescr']);

    if (!$device) {
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
            $interface['label'] = $interface['label'].' '.$interface['ifIndex'];
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
            if (preg_match($reg.'i', $interface['label'])) {
                $interface['label'] = preg_replace($reg.'i', $val, $interface['label']);
            }
        }
    }

    return $interface;
}

function translate_ifOperStatus($ifOperStatus)
{
    $translate_ifOperStatus = array(
        '1' => 'up',
        '2' => 'down',
        '3' => 'testing',
        '4' => 'unknown',
        '5' => 'dormant',
        '6' => 'notPresent',
        '7' => 'lowerLayerDown',
    );

    if (isset($translate_ifOperStatus[$ifOperStatus])) {
        $ifOperStatus = $translate_ifOperStatus[$ifOperStatus];
    }

    return $ifOperStatus;
}


function translate_ifAdminStatus($ifAdminStatus)
{
    $translate_ifAdminStatus = array(
        '1' => 'up',
        '2' => 'down',
        '3' => 'testing',
    );

    if (isset($translate_ifAdminStatus[$ifAdminStatus])) {
        $ifAdminStatus = $translate_ifAdminStatus[$ifAdminStatus];
    }

    return $ifAdminStatus;
}


// Specific rewrite functions

function makeshortif($if)
{
    $rewrite_shortif = array(
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
    );

    $if = fixifName($if);
    $if = strtolower($if);
    $if = array_str_replace($rewrite_shortif, $if);
    return $if;
}


function rewrite_ios_features($features)
{
    $rewrite_ios_features = array(
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
    );

    $type = array_preg_replace($rewrite_ios_features, $features);

    return ($features);
}


function rewrite_extreme_hardware($hardware)
{
    $rewrite_extreme_hardware = array(
        '.1.3.6.1.4.1.1916.2.1'   => 'Summit 1',
        '.1.3.6.1.4.1.1916.2.2'   => 'Summit 2',
        '.1.3.6.1.4.1.1916.2.3'   => 'Summit 3',
        '.1.3.6.1.4.1.1916.2.4'   => 'Summit 4',
        '.1.3.6.1.4.1.1916.2.5'   => 'Summit 4FX',
        '.1.3.6.1.4.1.1916.2.6'   => 'Summit 48',
        '.1.3.6.1.4.1.1916.2.7'   => 'Summit 24',
        '.1.3.6.1.4.1.1916.2.8'   => 'BlackDiamond 6800',
        '.1.3.6.1.4.1.1916.2.11'  => 'BlackDiamond 6808',
        '.1.3.6.1.4.1.1916.2.12'  => 'Summit 7iSX',
        '.1.3.6.1.4.1.1916.2.13'  => 'Summit 7iTX',
        '.1.3.6.1.4.1.1916.2.14'  => 'Summit 1iTX',
        '.1.3.6.1.4.1.1916.2.15'  => 'Summit 5i',
        '.1.3.6.1.4.1.1916.2.16'  => 'Summit 48i',
        '.1.3.6.1.4.1.1916.2.17'  => 'Alpine 3808',
        '.1.3.6.1.4.1.1916.2.19'  => 'Summit 1iSX',
        '.1.3.6.1.4.1.1916.2.20'  => 'Alpine 3804',
        '.1.3.6.1.4.1.1916.2.21'  => 'Summit 5iLX',
        '.1.3.6.1.4.1.1916.2.22'  => 'Summit 5iTX',
        '.1.3.6.1.4.1.1916.2.23'  => 'EnetSwitch 24Port',
        '.1.3.6.1.4.1.1916.2.24'  => 'BlackDiamond 6816',
        '.1.3.6.1.4.1.1916.2.25'  => 'Summit 24e3',
        '.1.3.6.1.4.1.1916.2.26'  => 'Alpine 3802',
        '.1.3.6.1.4.1.1916.2.27'  => 'BlackDiamond 6804',
        '.1.3.6.1.4.1.1916.2.28'  => 'Summit 48i1u',
        '.1.3.6.1.4.1.1916.2.30'  => 'Summit Px1',
        '.1.3.6.1.4.1.1916.2.40'  => 'Summit 24e2TX',
        '.1.3.6.1.4.1.1916.2.41'  => 'Summit 24e2SX',
        '.1.3.6.1.4.1.1916.2.53'  => 'Summit 200-24',
        '.1.3.6.1.4.1.1916.2.54'  => 'Summit 200-48',
        '.1.3.6.1.4.1.1916.2.55'  => 'Summit 300-48',
        '.1.3.6.1.4.1.1916.2.56'  => 'BlackDiamond 10808',
        '.1.3.6.1.4.1.1916.2.58'  => 'Summit 400-48t',
        '.1.3.6.1.4.1.1916.2.59'  => 'Summit 400-24x',
        '.1.3.6.1.4.1.1916.2.61'  => 'Summit 300-24',
        '.1.3.6.1.4.1.1916.2.62'  => 'BlackDiamond 8810',
        '.1.3.6.1.4.1.1916.2.63'  => 'Summit 400-24t',
        '.1.3.6.1.4.1.1916.2.64'  => 'Summit 400-24p',
        '.1.3.6.1.4.1.1916.2.65'  => 'Summit X450-24x',
        '.1.3.6.1.4.1.1916.2.66'  => 'Summit X450-24t',
        '.1.3.6.1.4.1.1916.2.67'  => 'SummitStack',
        '.1.3.6.1.4.1.1916.2.68'  => 'SummitWM 100',
        '.1.3.6.1.4.1.1916.2.69'  => 'SummitWM 1000',
        '.1.3.6.1.4.1.1916.2.70'  => 'Summit 200-24fx',
        '.1.3.6.1.4.1.1916.2.71'  => 'Summit X450a-24t',
        '.1.3.6.1.4.1.1916.2.72'  => 'Summit X450e-24p',
        '.1.3.6.1.4.1.1916.2.74'  => 'BlackDiamond 8806',
        '.1.3.6.1.4.1.1916.2.75'  => 'Altitude 350',
        '.1.3.6.1.4.1.1916.2.76'  => 'Summit X450a-48t',
        '.1.3.6.1.4.1.1916.2.77'  => 'BlackDiamond 12804',
        '.1.3.6.1.4.1.1916.2.79'  => 'Summit X450e-48p',
        '.1.3.6.1.4.1.1916.2.80'  => 'Summit X450a-24tDC',
        '.1.3.6.1.4.1.1916.2.81'  => 'Summit X450a-24t',
        '.1.3.6.1.4.1.1916.2.82'  => 'Summit X450a-24xDC',
        '.1.3.6.1.4.1.1916.2.83'  => 'Sentriant CE150',
        '.1.3.6.1.4.1.1916.2.84'  => 'Summit X450a-24x',
        '.1.3.6.1.4.1.1916.2.85'  => 'BlackDiamond 12802',
        '.1.3.6.1.4.1.1916.2.86'  => 'Altitude 300',
        '.1.3.6.1.4.1.1916.2.87'  => 'Summit X450a-48tDC',
        '.1.3.6.1.4.1.1916.2.88'  => 'Summit X250-24t',
        '.1.3.6.1.4.1.1916.2.89'  => 'Summit X250-24p',
        '.1.3.6.1.4.1.1916.2.90'  => 'Summit X250-24x',
        '.1.3.6.1.4.1.1916.2.91'  => 'Summit X250-48t',
        '.1.3.6.1.4.1.1916.2.92'  => 'Summit X250-48p',
        '.1.3.6.1.4.1.1916.2.93'  => 'Summit Ver2Stack',
        '.1.3.6.1.4.1.1916.2.94'  => 'SummitWM 200',
        '.1.3.6.1.4.1.1916.2.95'  => 'SummitWM 2000',
        '.1.3.6.1.4.1.1916.2.100' => 'Summit x150-24t',
        '.1.3.6.1.4.1.1916.2.114' => 'Summit x650-24x',
        '.1.3.6.1.4.1.1916.2.118' => 'Summit X650-24x(SSns)',
        '.1.3.6.1.4.1.1916.2.120' => 'Summit x650-24x(SS)',
        '.1.3.6.1.4.1.1916.2.129' => 'NWI-e450a',
        '.1.3.6.1.4.1.1916.2.133' => 'Summit x480-48t',
        '.1.3.6.1.4.1.1916.2.137' => 'Summit X480-24x',
        '.1.3.6.1.4.1.1916.2.139' => 'Summit X480-24x(10G4X)',
        '.1.3.6.1.4.1.1916.2.141' => 'Summit x480-48x',
        '.1.3.6.1.4.1.1916.2.167' => 'Summit x670-48x',
        '.1.3.6.1.4.1.1916.2.168' => 'Summit x670v-48x',
    );

    // $hardware = array_str_replace($rewrite_extreme_hardware, $hardware);
    $hardware = $rewrite_extreme_hardware[$hardware];

    return ($hardware);
}


function rewrite_junose_hardware($hardware)
{
    $rewrite_junose_hardware = array(
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
    );


    $hardware = array_str_replace($rewrite_junose_hardware, $hardware);

    return ($hardware);
}


function rewrite_generic_hardware($hardware)
{
    $rewrite_GenericHW = array(
        ' Computer Corporation' => '',
        ' Corporation'          => '',
        ' Inc.'                 => '',
    );
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
    $rewrite_hrDevice = array(
        'GenuineIntel:' => '',
        'AuthenticAMD:' => '',
        'Intel(R)'      => '',
        'CPU'           => '',
        '(R)'           => '',
        '  '            => ' ',
    );

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
    $desc       = trim($desc);

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
    $adslLineTypes = array(
        'noChannel'          => 'No Channel',
        'fastOnly'           => 'Fastpath',
        'interleavedOnly'    => 'Interleaved',
        'fastOrInterleaved'  => 'Fast/Interleaved',
        'fastAndInterleaved' => 'Fast+Interleaved',
    );

    foreach ($adslLineTypes as $type => $text) {
        if ($adslLineType == $type) {
            $adslLineType = $text;
        }
    }

    return ($adslLineType);
}

function ipmiSensorName($hardwareId, $sensorIpmi)
{
    $ipmiSensorsNames = array(
        "HP ProLiant BL460c G6" => array(
            "Temp 1" => "Ambient zone",
            "Temp 2" => "CPU 1",
            "Temp 3" => "CPU 2",
            "Temp 4" => "Memory zone",
            "Temp 5" => "Memory zone",
            "Temp 6" => "Memory zone",
            "Temp 7" => "System zone",
            "Temp 8" => "System zone",
            "Temp 9" => "System zone",
            "Temp 10" => "Storage zone",
            "Power Meter" => "Power usage",
        ),
        "HP ProLiant BL460c G1" => array(
            "Temp 1" => "System zone",
            "Temp 2" => "CPU 1 zone",
            "Temp 3" => "CPU 1",
            "Temp 4" => "CPU 1",
            "Temp 5" => "CPU 2 zone",
            "Temp 6" => "CPU 2",
            "Temp 7" => "CPU 2",
            "Temp 8" => "Memory zone",
            "Temp 9" => "Ambient zone",
            "Power Meter" => "Power usage",
        ),
    );

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
    if (!empty($temp_response[0])) {
        $value = $temp_response[0];
    }
    return $value;
}

function parse_entity_state($state, $value)
{
    $data = array(
        'entStateOper' => array(
            1 => array('text' => 'unavailable', 'color' => 'default'),
            2 => array('text' => 'disabled', 'color' => 'danger'),
            3 => array('text' => 'enabled', 'color' => 'success'),
            4 => array('text' => 'testing', 'color' => 'warning'),
        ),
        'entStateUsage' => array(
            1 => array('text' => 'unavailable', 'color' => 'default'),
            2 => array('text' => 'idle', 'color' => 'info'),
            3 => array('text' => 'active', 'color' => 'success'),
            4 => array('text' => 'busy', 'color' => 'success'),
        ),
        'entStateStandby' => array(
            1 => array('text' => 'unavailable', 'color' => 'default'),
            2 => array('text' => 'hotStandby', 'color' => 'info'),
            3 => array('text' => 'coldStandby', 'color' => 'info'),
            4 => array('text' => 'providingService', 'color' => 'success'),
        ),
        'entStateAdmin' => array(
            1 => array('text' => 'unknown', 'color' => 'default'),
            2 => array('text' => 'locked', 'color' => 'info'),
            3 => array('text' => 'shuttingDown', 'color' => 'warning'),
            4 => array('text' => 'unlocked', 'color' => 'success'),
        ),
    );

    if (isset($data[$state][$value])) {
        return $data[$state][$value];
    }

    return array('text'=>'na', 'color'=>'default');
}

function parse_entity_state_alarm($bits)
{
    // not sure if this is correct
    $data = array(
        0 => array('text' => 'unavailable', 'color' => 'default'),
        1 => array('text' => 'underRepair', 'color' => 'warning'),
        2 => array('text' => 'critical', 'color' => 'danger'),
        3 => array('text' => 'major', 'color' => 'danger'),
        4 => array('text' => 'minor', 'color' => 'info'),
        5 => array('text' => 'warning', 'color' => 'warning'),
        6 => array('text' => 'indeterminate', 'color' => 'default'),
    );

    $alarms = str_split(base_convert($bits, 16, 2));
    $active_alarms = array_filter($alarms);
    return array_intersect_key($data, $active_alarms);
}
