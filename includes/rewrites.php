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


function rewrite_junos_hardware($hardware)
{
    $rewrite_junos_hardware = array(
        'jnxProductACX1000'                 => 'ACX1000',
        'jnxProductACX1100'                 => 'ACX1100',
        'jnxProductACX2000'                 => 'ACX2000',
        'jnxProductACX2100'                 => 'ACX2100',
        'jnxProductACX2200'                 => 'ACX2200',
        'jnxProductACX4000'                 => 'ACX4000',
        'jnxProductACX500AC'                => 'ACX500AC',
        'jnxProductACX500DC'                => 'ACX500DC',
        'jnxProductACX500IAC'               => 'ACX500IAC',
        'jnxProductACX500IDC'               => 'ACX500IDC',
        'jnxProductACX500OAC'               => 'ACX500OAC',
        'jnxProductACX500ODC'               => 'ACX500ODC',
        'jnxProductACX500OPOEAC'            => 'ACX500OPOEAC',
        'jnxProductACX500OPOEDC'            => 'ACX500OPOEDC',
        'jnxProductACX5048'                 => 'ACX5048',
        'jnxProductACX5096'                 => 'ACX5096',
        'jnxProductACX5448'                 => 'ACX5448',
        'jnxProductEX2200Cport12P'          => 'EX2200C-12P',
        'jnxProductEX2200Cport12T'          => 'EX2200C-12T',
        'jnxProductEX2200port24P'           => 'EX2200-24P',
        'jnxProductEX2200port24T'           => 'EX2200-24T',
        'jnxProductEX2200port24TDC'         =>  'EX2200-24TDC',
        'jnxProductEX2200port48P'           => 'EX2200-48P',
        'jnxProductEX2200port48T'           => 'EX2200-48T',
        'jnxProductEX2300Cport12P'          => 'EX2300C-12P',
        'jnxProductEX2300Cport12T'          => 'EX2300C-12T',
        'jnxProductEX2300port24MP'          => 'EX2300-24MP',
        'jnxProductEX2300port24P'           => 'EX2300-24P',
        'jnxProductEX2300port24T'           => 'EX2300-24T',
        'jnxProductEX2300port48MP'          => 'EX2300-48MP',
        'jnxProductEX2300port48P'           => 'EX2300-48P',
        'jnxProductEX2300port48T'           => 'EX2300-48T',
        'jnxProductEX3200port24P'           => 'EX3200-24P',
        'jnxProductEX3200port24T'           => 'EX3200-24T',
        'jnxProductEX3200port48P'           => 'EX3200-48P',
        'jnxProductEX3200port48T'           => 'EX3200-48T',
        'jnxProductEX3300port24P'           => 'EX3300-24P',
        'jnxProductEX3300port24T'           => 'EX3300-24T',
        'jnxProductEX3300port24TDC'         => 'EX3300-24TDC',
        'jnxProductEX3300port48P'           => 'EX3300-48P',
        'jnxProductEX3300port48T'           => 'EX3300-48T',
        'jnxProductEX3300port48TBF'         => 'EX3300-48TBF',
        'jnxProductEX3400port24P'           => 'EX3400-24P',
        'jnxProductEX3400port24T'           => 'EX3400-24T',
        'jnxProductEX3400port48P'           => 'EX3400-48P',
        'jnxProductEX3400port48T'           => 'EX3400-48T',
        'jnxProductEX4200port24F'           => 'EX4200-24F',
        'jnxProductEX4200port24P'           => 'EX4200-24P',
        'jnxProductEX4200port24PX'          => 'EX4200-24PX',
        'jnxProductEX4200port24T'           => 'EX4200-24T',
        'jnxProductEX4200port48P'           => 'EX4200-48P',
        'jnxProductEX4200port48PX'          => 'EX4200-48PX',
        'jnxProductEX4200port48T'           => 'EX4200-48T',
        'jnxProductEX4300port24P'           => 'EX4300-24P',
        'jnxProductEX4300port24T'           => 'EX4300-24T',
        'jnxProductEX4300port32F'           => 'EX4300-32F',
        'jnxProductEX4300port48MP'          => 'EX4300-48MP',
        'jnxProductEX4300port48P'           => 'EX4300-48P',
        'jnxProductEX4300port48T'           => 'EX4300-48T',
        'jnxProductEX4300port48TBF'         => 'EX4300-48TBF',
        'jnxProductEX4300port48TDC'         => 'EX4300-48TDC',
        'jnxProductEX4300port48TDCBF'       => 'EX4300-48TDCBF',
        'jnxProductEX4500port20F'           => 'EX4500-20F',
        'jnxProductEX4500port40F'           => 'EX4500-40F',
        'jnxProductEX4550port32F'           => 'EX4550-32F',
        'jnxProductEX4550port32T'           => 'EX4550-32T',
        'jnxProductEX4600'                  => 'EX4600',
        'jnxProductEX465048Y8C'             => 'EX465048Y8C',
        'jnxProductEXXRE'                   => 'EXXRE',
        'jnxProductFX1600port'              => 'FX1600',
        'jnxProductFX2160port'              => 'FX2160',
        'jnxProductIBM0719J45Eport20F'      => 'IBM0719J45E-20F',
        'jnxProductIBM0719J45Eport40F'      => 'IBM0719J45E-40F',
        'jnxProductIBM2409F52J52F'          => 'IBM2409F52J52F',
        'jnxProductIBM2413F08J08F'          => 'IBM2413F08J08F',
        'jnxProductIBM427348EJ48Eport24F'   => 'IBM427348EJ48E-24F',
        'jnxProductIBM427348EJ48Eport24P'   => 'IBM427348EJ48E-24P',
        'jnxProductIBM427348EJ48Eport24T'   => 'IBM427348EJ48E-24T',
        'jnxProductIBM427348EJ48Eport48P'   => 'IBM427348EJ48E-48P',
        'jnxProductIBM427348EJ48Eport48T'   => 'IBM427348EJ48E-48T',
        'jnxProductIBM8729HC1J52F'          => 'IBM8729HC1J52F',
        'jnxProductMX10'                    => 'MX10',
        'jnxProductMX104'                   => 'MX104',
        'jnxProductMX40'                    => 'MX40',
        'jnxProductMX5'                     => 'MX5',
        'jnxProductMX80'                    => 'MX80',
        'jnxProductMX80-48T'                => 'MX80-48T',
        'jnxProductMX80-P'                  => 'MX80-P',
        'jnxProductMX80-T'                  => 'MX80-T',
        'jnxProductMXTSR80'                 => 'MXTSR80',
        'jnxProductNFX150CS1'               => 'NFX150CS1',
        'jnxProductNFX150CS1AA'             => 'NFX150CS1AA',
        'jnxProductNFX150CS1AE'             => 'NFX150CS1AE',
        'jnxProductNFX150CS1EAA'            => 'NFX150CS1EAA',
        'jnxProductNFX150CS1EAE'            => 'NFX150CS1EAE',
        'jnxProductNFX150S1'                => 'NFX150S1',
        'jnxProductNFX150S1E'               => 'NFX150S1E',
        'jnxProductNFX250ATTLS1'            => 'NFX250ATTLS1',
        'jnxProductNFX250ATTS1'             => 'NFX250ATTS1',
        'jnxProductNFX250ATTS2'             => 'NFX250ATTS2',
        'jnxProductNFX250LS1'               => 'NFX250LS1',
        'jnxProductNFX250S1'                => 'NFX250S1',
        'jnxProductNFX250S1E'               => 'NFX250S1E',
        'jnxProductNFX250S2'                => 'NFX250S2',
        'jnxProductNFX350S1'                => 'NFX350S1',
        'jnxProductNFX350S2'                => 'NFX350S2',
        'jnxProductNFX350S3'                => 'NFX350S3',
        'jnxProductNFXOPAL'                 => 'NFXOPAL',
        'jnxProductNFXVirtual'              => 'NFXVirtual',
        'jnxProductNFXWhiteBox1'            => 'NFXWhiteBox1',
        'jnxProductName'                    => '',
        'jnxProductNameACX'                 => 'ACX',
        'jnxProductNameACX1000'             => 'ACX1000',
        'jnxProductNameACX1100'             => 'ACX1100',
        'jnxProductNameACX2000'             => 'ACX2000',
        'jnxProductNameACX2100'             => 'ACX2100',
        'jnxProductNameACX2200'             => 'ACX2200',
        'jnxProductNameACX4000'             => 'ACX4000',
        'jnxProductNameACX500AC'            => 'ACX500AC',
        'jnxProductNameACX500DC'            => 'ACX500DC',
        'jnxProductNameACX500OAC'           => 'ACX500OAC',
        'jnxProductNameACX500ODC'           => 'ACX500ODC',
        'jnxProductNameACX500OPOEAC'        => 'ACX500OPOEAC',
        'jnxProductNameACX500OPOEDC'        => 'ACX500OPOEDC',
        'jnxProductNameACX5048'             => 'ACX5048',
        'jnxProductNameACX5096'             => 'ACX5096',
        'jnxProductNameACX5448'             => 'ACX5448',
        'jnxProductNameACX6360OR'           => 'ACX6360OR',
        'jnxProductNameACX6360OX'           => 'ACX6360OX',
        'jnxProductNameDELLJSRX1400'        => 'DELLJSRX1400',
        'jnxProductNameDELLJSRX3400'        => 'DELLJSRX3400',
        'jnxProductNameDELLJSRX3600'        => 'DELLJSRX3600',
        'jnxProductNameDELLJSRX5400'        => 'DELLJSRX5400',
        'jnxProductNameDELLJSRX5600'        => 'DELLJSRX5600',
        'jnxProductNameDELLJSRX5800'        => 'DELLJSRX5800',
        'jnxProductNameDellJFX3500'         => 'DellJFX3500',
        'jnxProductNameESR1000V'            => 'ESR1000V',
        'jnxProductNameEX2200'              => 'EX2200',
        'jnxProductNameEX2300'              => 'EX2300',
        'jnxProductNameEX3200'              => 'EX3200',
        'jnxProductNameEX3300'              => 'EX3300',
        'jnxProductNameEX3400'              => 'EX3400',
        'jnxProductNameEX4200'              => 'EX4200',
        'jnxProductNameEX4300'              => 'EX4300',
        'jnxProductNameEX4500'              => 'EX4500',
        'jnxProductNameEX4550'              => 'EX4550',
        'jnxProductNameEX4600'              => 'EX4600',
        'jnxProductNameEX4650'              => 'EX4650',
        'jnxProductNameEX6210'              => 'EX6210',
        'jnxProductNameEX8208'              => 'EX8208',
        'jnxProductNameEX8216'              => 'EX8216',
        'jnxProductNameEX9204'              => 'EX9204',
        'jnxProductNameEX9208'              => 'EX9208',
        'jnxProductNameEX9214'              => 'EX9214',
        'jnxProductNameEX9251'              => 'EX9251',
        'jnxProductNameEX9253'              => 'EX9253',
        'jnxProductNameEXXRE'               => 'EXXRE',
        'jnxProductNameFXSeries'            => 'FXSeries',
        'jnxProductNameFireflyPerimeter'    => 'FireflyPerimeter',
        'jnxProductNameIBM0719J45E'         => 'IBM0719J45E',
        'jnxProductNameIBM427348EJ48E'      => 'IBM427348EJ48E',
        'jnxProductNameIBM4274E08J08E'      => 'IBM4274E08J08E',
        'jnxProductNameIBM4274E16J16E'      => 'IBM4274E16J16E',
        'jnxProductNameIBM4274M02J02M'      => 'IBM4274M02J02M',
        'jnxProductNameIBM4274M06J06M'      => 'IBM4274M06J06M',
        'jnxProductNameIBM4274M11J11M'      => 'IBM4274M11J11M',
        'jnxProductNameIBM4274S34J34S'      => 'IBM4274S34J34S',
        'jnxProductNameIBM4274S36J36S'      => 'IBM4274S36J36S',
        'jnxProductNameIBM4274S54J54S'      => 'IBM4274S54J54S',
        'jnxProductNameIBM4274S56J56S'      => 'IBM4274S56J56S',
        'jnxProductNameIBM4274S58J58S'      => 'IBM4274S58J58S',
        'jnxProductNameIBMJ08F'             => 'IBMJ08F',
        'jnxProductNameIBMJ52F'             => 'IBMJ52F',
        'jnxProductNameIRM'                 => 'IRM',
        'jnxProductNameJ2300'               => 'J2300',
        'jnxProductNameJ2320'               => 'J2320',
        'jnxProductNameJ2350'               => 'J2350',
        'jnxProductNameJ4300'               => 'J4300',
        'jnxProductNameJ4320'               => 'J4320',
        'jnxProductNameJ4350'               => 'J4350',
        'jnxProductNameJ6300'               => 'J6300',
        'jnxProductNameJ6350'               => 'J6350',
        'jnxProductNameJCS'                 => 'JCS',
        'jnxProductNameJNP10001'            => 'JNP10001',
        'jnxProductNameJNP10003'            => 'JNP10003',
        'jnxProductNameJNP204'              => 'JNP204',
        'jnxProductNameJRR200'              => 'JRR200',
        'jnxProductNameLN1000CC'            => 'LN1000CC',
        'jnxProductNameLN1000V'             => 'LN1000V',
        'jnxProductNameLN2600'              => 'LN2600',
        'jnxProductNameLN2800'              => 'LN2800',
        'jnxProductNameM10'                 => 'M10',
        'jnxProductNameM10i'                => 'M10i',
        'jnxProductNameM120'                => 'M120',
        'jnxProductNameM160'                => 'M160',
        'jnxProductNameM20'                 => 'M20',
        'jnxProductNameM320'                => 'M320',
        'jnxProductNameM40'                 => 'M40',
        'jnxProductNameM40e'                => 'M40e',
        'jnxProductNameM5'                  => 'M5',
        'jnxProductNameM7i'                 => 'M7i',
        'jnxProductNameMAG6610'             => 'MAG6610',
        'jnxProductNameMAG6611'             => 'MAG6611',
        'jnxProductNameMAG8600'             => 'MAG8600',
        'jnxProductNameMX10'                => 'MX10',
        'jnxProductNameMX10008'             => 'MX10008',
        'jnxProductNameMX10016'             => 'MX10016',
        'jnxProductNameMX104'               => 'MX104',
        'jnxProductNameMX10440G'            => 'MX10440G',
        'jnxProductNameMX150'               => 'MX150',
        'jnxProductNameMX2008'              => 'MX2008',
        'jnxProductNameMX2010'              => 'MX2010',
        'jnxProductNameMX2020'              => 'MX2020',
        'jnxProductNameMX240'               => 'MX240',
        'jnxProductNameMX40'                => 'MX40',
        'jnxProductNameMX480'               => 'MX480',
        'jnxProductNameMX5'                 => 'MX5',
        'jnxProductNameMX80'                => 'MX80',
        'jnxProductNameMX960'               => 'MX960',
        'jnxProductNameMXTSR80'             => 'MXTSR80',
        'jnxProductNameNFX'                 => 'NFX',
        'jnxProductNameOCPAcc'              => 'OCPAcc',
        'jnxProductNamePTX1000'             => 'PTX1000',
        'jnxProductNamePTX1000260C'         => 'PTX10002-60C',
        'jnxProductNamePTX10008'            => 'PTX10008',
        'jnxProductNamePTX10016'            => 'PTX10016',
        'jnxProductNamePTX3000'             => 'PTX3000',
        'jnxProductNamePTX5000'             => 'PTX5000',
        'jnxProductNameQFX1000260C'         => 'QFX10002-60C',
        'jnxProductNameQFX3000'             => 'QFX3000',
        'jnxProductNameQFX3100'             => 'QFX3100',
        'jnxProductNameQFX5000'             => 'QFX5000',
        'jnxProductNameQFXInterconnect'     => 'QFXInterconnect',
        'jnxProductNameQFXJVRE'             => 'QFXJVRE',
        'jnxProductNameQFXMInterconnect'    => 'QFXMInterconnect',
        'jnxProductNameQFXNode'             => 'QFXNode',
        'jnxProductNameQFXSwitch'           => 'QFXSwitch',
        'jnxProductNameSRX100'              => 'SRX100',
        'jnxProductNameSRX110'              => 'SRX110',
        'jnxProductNameSRX120'              => 'SRX120',
        'jnxProductNameSRX1400'             => 'SRX1400',
        'jnxProductNameSRX1500'             => 'SRX1500',
        'jnxProductNameSRX210'              => 'SRX210',
        'jnxProductNameSRX220'              => 'SRX220',
        'jnxProductNameSRX240'              => 'SRX240',
        'jnxProductNameSRX300'              => 'SRX300',
        'jnxProductNameSRX320'              => 'SRX320',
        'jnxProductNameSRX340'              => 'SRX340',
        'jnxProductNameSRX3400'             => 'SRX3400',
        'jnxProductNameSRX345'              => 'SRX345',
        'jnxProductNameSRX3600'             => 'SRX3600',
        'jnxProductNameSRX4100'             => 'SRX4100',
        'jnxProductNameSRX4200'             => 'SRX4200',
        'jnxProductNameSRX4600'             => 'SRX4600',
        'jnxProductNameSRX4800'             => 'SRX4800',
        'jnxProductNameSRX5400'             => 'SRX5400',
        'jnxProductNameSRX550'              => 'SRX550',
        'jnxProductNameSRX5600'             => 'SRX5600',
        'jnxProductNameSRX5800'             => 'SRX5800',
        'jnxProductNameSRX650'              => 'SRX650',
        'jnxProductNameSatelliteDevice'     => 'SatelliteDevice',
        'jnxProductNameT1600'               => 'T1600',
        'jnxProductNameT320'                => 'T320',
        'jnxProductNameT4000'               => 'T4000',
        'jnxProductNameT640'                => 'T640',
        'jnxProductNameTX'                  => 'TX',
        'jnxProductNameTXP'                 => 'TXP',
        'jnxProductNameVMX'                 => 'VMX',
        'jnxProductNameVRR'                 => 'VRR',
        'jnxProductNameVSRX'                => 'VSRX',
        'jnxProductNameVseries'             => 'Vseries',
        'jnxProductOCP48S'                  => 'OCP48S',
        'jnxProductOCP48T'                  => 'OCP48T',
        'jnxProductQFX1000236Q'             => 'QFX10002-36Q',
        'jnxProductQFX1000272Q'             => 'QFX10002-72Q',
        'jnxProductQFX10004'                => 'QFX10004',
        'jnxProductQFX10008'                => 'QFX10008',
        'jnxProductQFX10016'                => 'QFX10016',
        'jnxProductQFX3000-G'               => 'QFX3000-G',
        'jnxProductQFX3000-M'               => 'QFX3000-M',
        'jnxProductQFX3008'                 => 'QFX3008',
        'jnxProductQFX3008I'                => 'QFX3008I',
        'jnxProductQFX3500'                 => 'QFX3500',
        'jnxProductQFX350048T4Q'            => 'QFX3500-48T4Q',
        'jnxProductQFX350048T4QS'           => 'QFX3500-48T4QS',
        'jnxProductQFX3500s'                => 'QFX3500s',
        'jnxProductQFX360016Q'              => 'QFX3600-16Q',
        'jnxProductQFX360016QS'             => 'QFX3600-16QS',
        'jnxProductQFX3600I'                => 'QFX3600I',
        'jnxProductQFX510024Q'              => 'QFX5100-24Q',
        'jnxProductQFX510024QF'             => 'QFX5100-24QF',
        'jnxProductQFX510024QHP'            => 'QFX5100-24QHP',
        'jnxProductQFX510024QI'             => 'QFX5100-24QI',
        'jnxProductQFX510048C6Q'            => 'QFX5100-48C6Q',
        'jnxProductQFX510048C6QF'           => 'QFX5100-48C6QF',
        'jnxProductQFX510048S6Q'            => 'QFX5100-48S6Q',
        'jnxProductQFX510048S6QF'           => 'QFX5100-48S6QF',
        'jnxProductQFX510048T6Q'            => 'QFX5100-48T6Q',
        'jnxProductQFX510096S6QF'           => 'QFX5100-96S6QF',
        'jnxProductQFX510096S8Q'            => 'QFX5100-96S8Q',
        'jnxProductQFX511032Q'              => 'QFX5110-32Q',
        'jnxProductQFX511048S4C'            => 'QFX5110-48S4C',
        'jnxProductQFX512048Y8C'            => 'QFX5120-48Y8C',
        'jnxProductQFX520032C32Q'           => 'QFX5200-32C-32Q',
        'jnxProductQFX520032C64Q'           => 'QFX5200-32C-64Q',
        'jnxProductQFX520048Y'              => 'QFX5200-48Y',
        'jnxProductQFX521064C'              => 'QFX5210-64C',
        'jnxProductQFX5500'                 => 'QFX5500',
        'jnxProductQFXC083008'              => 'QFXC083008',
    );


    $hardware = array_str_replace($rewrite_junos_hardware, $hardware);

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
