#!/usr/bin/env php
<?php

use LibreNMS\Proc;

global $device;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$options = getopt(
    'h:dm:o:v:f:',
    array(
        'debug',
        'hostname:',
        'help',
        'module:',
        'os:',
        'variant:',
        'file:',
    )
);

$debug = (isset($options['d']) || isset($options['debug']));
$vdebug = $debug;
$snmpsim_ip = '127.1.6.1';

$init_modules = array('discovery');
require $install_dir . '/includes/init.php';

if (isset($options['h'])) {
    $hostname = $options['h'];
} elseif (isset($options['hostname'])) {
    $hostname = $options['hostname'];
}

if (isset($options['o'])) {
    $target_os = $options['o'];
} elseif (isset($options['os'])) {
    $target_os = $options['os'];
}

if (isset($hostname)) {
    if (is_numeric($hostname)) {
        $device = device_by_id_cache($hostname);
    } elseif (!empty($hostname)) {
        $device = device_by_name($hostname);
    }

    if (isset($device['os'])) {
        $target_os = $device['os'];
    }
}

if (isset($options['help']) || !isset($target_os)) {
    echo "Insert help here\n";
    exit;
}

$module = 'all';
if (isset($options['m'])) {
    $module = $options['m'];
} elseif (isset($options['module'])) {
    $module = $options['module'];
}

$variant = '';
if (isset($options['v'])) {
    $variant = '_' . $options['v'];
} elseif (isset($options['variant'])) {
    $variant = '_' . $options['variant'];
}

$output_file = $install_dir . "/tests/data/$target_os$variant.json";
if (isset($options['f'])) {
    $output_file = $options['f'];
} elseif(isset($options['file'])) {
    $output_file = $options['file'];
}

// capture data

if ($device) {
    $snmprec_file = $install_dir . "/tests/snmpsim/$target_os$variant.snmprec";

    // Run discovery
    ob_start();
    $save_debug = $debug;
    $save_vedbug = $vdebug;
    $debug = true;
    $vdebug = false;
    discover_device($device, $module == 'all' ? array() : array('m' => $module));
    $debug = $save_debug;
    $vdebug = $save_vedbug;
    $discover_output = ob_get_contents();
    ob_end_clean();

    if ($debug) {
        echo $discover_output . PHP_EOL;
    }

    // remove color
    $discover_output = preg_replace('/\033\[[\d;]+m/', '', $discover_output);

    // extract snmp queries
    preg_match_all('/SNMP\[.*snmp(bulk)?([a-z]+) .+:HOSTNAME:[0-9]+ ([0-9.a-zA-Z:\-]+)\]/', $discover_output, $snmp_matches);

    // extract mibs and group with oids
    $snmp_oids = array();
    foreach ($snmp_matches[0] as $index => $line) {
        preg_match('/-m ([a-zA-Z:\-]+)/', $line, $mib_matches);
        $snmp_oids[] = array(
            'oid' => $snmp_matches[3][$index],
            'mib' => $mib_matches[1],
            'method' => $snmp_matches[2][$index],
        );
    }

    $snmprec_data = array();
    foreach ($snmp_oids as $oid_data) {
        $options = '-OUneb';
        if ($oid_data['method'] == 'walk') {
            $data = snmp_walk($device, $oid_data['oid'], $options, $oid_data['mib']);
        } elseif ($oid_data['method'] == 'get') {
            $data = snmp_get($device, $oid_data['oid'], $options, $oid_data['mib']);
        } elseif ($oid_data['method'] == 'getnext') {
            $data = snmp_getnext($device, $oid_data['oid'], $options, $oid_data['mib']);
        }
        if (isset($data) && $data) {
            $snmprec_data[] = convert_snmpwalk_to_snmprec($data);
        }

    }

    merge_snmprec_data($snmprec_file, $snmprec_data);
} else {
    $snmpsim_log = "/tmp/snmpsimd.log";
    $snmpsim_cmd = "snmpsimd.py --data-dir=./tests/snmpsim --agent-udpv4-endpoint=$snmpsim_ip:1161 --logging-method=file:$snmpsim_log";
    echo "Starting snmpsimd...\n";
    d_echo($snmpsim_cmd);
    $proc_snmpsimd = new Proc($snmpsim_cmd);
    echo "Logfile: $snmpsim_log\n";

    // Remove existing device in case it didn't get removed previously
    if ($existing_device = device_by_name($snmpsim_ip)) {
        delete_device($existing_device['device_id']);
    }

    try {
        // Add the test device
        $config['snmp']['community'] = array($target_os . $variant);
        $device_id = addHost($snmpsim_ip, 'v2c', 1161);
        echo "Added device: $device_id\n";
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
        exit;
    }

    // Populate the device variable
    $device = device_by_id_cache($device_id);

    // Run discovery
    discover_device($device, $module == 'all' ? array() : array('m' => $module));

}


// Dump the discovered data
$data = dump_module_data($device['device_id'], $module);

if ($device['hostname'] == $snmpsim_ip) {
    // Remove the test device
    echo delete_device($device['device_id'])."\n";
}

d_echo($data);

// Save the data to the default test data location (or elsewhere if specified)
file_put_contents($output_file, _json_encode($data));
echo "Saved to $output_file\n";
echo "Ready for testing!\n";


function convert_snmpwalk_to_snmprec($data) {
    $snmpTypes = array(
        'STRING' => '4',
        'OID' => '6',
        'Hex-STRING' => '4x',
        'Timeticks' => '67',
        'INTEGER' => '2',
        'OCTET STRING' => '4',
        'BITS' => '4', # not sure if this is right
        'Integer32' => '2',
        'NULL' => '5',
        'OBJECT IDENTIFIER' => '6',
        'IpAddress' => '64',
        'Counter32' => '65',
        'Gauge32' => '66',
        'Opaque' => '68',
        'Counter64' => '70',
        'Network Address' => '4'
    );

    $result = preg_replace_callback('/^\.?([0-9.]+) = (.+): (.*)$/', function ($matches) use ($snmpTypes) {
        $type_code = $snmpTypes[$matches[2]];

        // remove leading . from oid data
        if ($type_code == '6') {
            $matches[3] = ltrim($matches[3], '.');
        }

        return "{$matches[1]}|$type_code|{$matches[3]}";
    }, $data);

    return $result;
}


function merge_snmprec_data($file, array $data, $write = true)
{
    if (is_file($file)) {
        $existing_data = index_snmprec(file_get_contents($file));
    } else {
        $existing_data = array();
    }

    foreach ($data as $part) {
        array_merge($existing_data, index_snmprec($part));
    }

    usort($existing_data, 'compareOid');
    $output = implode(PHP_EOL, $existing_data) . PHP_EOL;

    if ($write) {
        echo "Updated snmprec data $file\n";
        file_put_contents($file, $output);
    }

    return $output;
}

function index_snmprec($snmprec_data)
{
    $result = array();

    $lines = explode("\n", $snmprec_data);
    foreach ($lines as $line) {
        list($oid, $type, $data) = explode('|', $line, 3);
        $result[$oid] = $line;
    }

    return $result;
}


function compareOid($a, $b)
{
    $a_oid = explode('.', $a);
    $b_oid = explode('.', $b);

    foreach ($a_oid as $index =>$a_part) {
        $b_part = $b_oid[$index];
        if ($a_part > $b_part) {
            return 1; // a is higher
        } elseif ($a_part < $b_part) {
            return -1; // b is higher
        }
    }

    if (count($a_oid) < count($b_oid)) {
        return -1; // same prefix, but b has more so it is higher
    }

    return 0;
}
