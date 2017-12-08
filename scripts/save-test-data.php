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
        'no-save',
        'hostname:',
        'help',
        'module:',
        'os:',
        'variant:',
        'file:',
    )
);

$init_modules = array('discovery');
require $install_dir . '/includes/init.php';

$debug = (isset($options['d']) || isset($options['debug']));
$vdebug = $debug;

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
    echo "Script to extract test data from devices or update test data

Usage:
  You must specify hostname or os.
  -h, --hostname   ID, IP, or hostname of the device to extract data from
                   If this is not given, the existing snmp data will be used
  -o, --os         Name of the OS to save test data for
  -v, --variant    The variant of the OS to use, usually the device model
  -m, --module     The discovery/poller module to collect data for
  -f, --file       File to save the database entries to.  Default is in tests/data/
  -d, --debug      Enable debug output
      --no-save    Don't save database entries, print them out instead
";
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
} elseif (isset($options['file'])) {
    $output_file = $options['file'];
}

// Capture snmp data
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
    $snmp_oids = array(
        array('oid' => 'sysDescr', 'mib' => 'SNMPv2-MIB', 'method' => 'getnext'),
        array('oid' => 'sysObjectID', 'mib' => 'SNMPv2-MIB', 'method' => 'getnext'),
    );
    foreach ($snmp_matches[0] as $index => $line) {
        preg_match('/-m ([a-zA-Z:\-]+)/', $line, $mib_matches);
        $snmp_oids[] = array(
            'oid' => $snmp_matches[3][$index],
            'mib' => $mib_matches[1],
            'method' => $snmp_matches[2][$index],
        );
    }

    echo "Capturing Data:";
    $snmprec_data = array();
    foreach ($snmp_oids as $oid_data) {
        echo " " . $oid_data['oid'];

        $options = '-OUneb';
        if ($oid_data['method'] == 'walk') {
            $data = snmp_walk($device, $oid_data['oid'], $options, $oid_data['mib']);
        } elseif ($oid_data['method'] == 'get') {
            $data = snmp_get($device, $oid_data['oid'], $options, $oid_data['mib']);
        } elseif ($oid_data['method'] == 'getnext') {
            $data = snmp_getnext($device, $oid_data['oid'], $options, $oid_data['mib']);
        }

        if (isset($data) && $data !== false) {
            $snmprec_data[] = convert_snmp_to_snmprec(explode(PHP_EOL, $data));
        }
    }
    echo PHP_EOL . PHP_EOL;

    save_snmprec_data($snmprec_file, $snmprec_data);

    echo PHP_EOL;
}


// Now use the saved data to update the saved database data
$snmpsim_ip = '127.1.6.1';
$snmpsim_log = "/tmp/snmpsimd.log";
$snmpsim_cmd = "snmpsimd.py --data-dir=./tests/snmpsim --agent-udpv4-endpoint=$snmpsim_ip:1161 --logging-method=file:$snmpsim_log";
echo "Starting snmpsimd... ";
d_echo($snmpsim_cmd);
$proc_snmpsimd = new Proc($snmpsim_cmd);
echo "Logfile: $snmpsim_log\n";
if (!$proc_snmpsimd->isRunning()) {
    echo `tail -5 $snmpsim_log` . PHP_EOL;
}


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

echo PHP_EOL;

// Dump the discovered data
$data = dump_module_data($device['device_id'], $module);

if ($device['hostname'] == $snmpsim_ip) {
    // Remove the test device
    $debug = false;
    $vdebug = false;
    echo delete_device($device['device_id'])."\n";
}

if (isset($options['no-save'])) {
    echo "Result: ";
    print_r($data);
} else {
    d_echo($data);

    // Save the data to the default test data location (or elsewhere if specified)
    $existing_data = json_decode(file_get_contents($output_file), true);

    foreach ($data as $module_name => $module_data) {
        $existing_data[$module_name] = $module_data;
    }

    file_put_contents($output_file, _json_encode($data));
    echo "Saved to $output_file\n";
    echo "Ready for testing!\n";
}


function convert_snmp_to_snmprec(array $snmp_data)
{
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

    $result = array();
    foreach ($snmp_data as $line) {
        if (str_contains($line, ' = ')) {
            preg_match('/^\.?([0-9.]+) = (.+): (.*)$/', $line, $matches);
            $oid = $matches[1];
            $type = $snmpTypes[$matches[2]];
            $data = $matches[3];

            // remove leading . from oid data
            if ($type == '6') {
                $data = ltrim($data, '.');
            }

            $result[] = "$oid|$type|$data";
        } else {
            // multi-line data, append to last
            $last = end($result);

            list($oid, $type, $data) = explode('|', $last, 3);
            if ($type == '4x') {
                $result[key($result)] .= bin2hex(PHP_EOL . $line);
            } else {
                $result[key($result)] = "$oid|4x|" . bin2hex($data . PHP_EOL . $line);
            }
        }
    }

    return $result;
}


function save_snmprec_data($file, array $data, $write = true)
{
    if (is_file($file)) {
        $existing_data = index_snmprec(explode(PHP_EOL, file_get_contents($file)));
    } else {
        $existing_data = array();
    }

    foreach ($data as $part) {
        $existing_data = array_merge($existing_data, index_snmprec($part));
    }

    usort($existing_data, 'compareOid');

    $output = implode(PHP_EOL, $existing_data) . PHP_EOL;

    if ($write) {
        echo "Updated snmprec data $file\n";
        echo "Verify these files do not contain any private data before submitting\n";
        file_put_contents($file, $output);
    }

    return $output;
}

function index_snmprec(array $snmprec_data)
{
    $result = array();

    foreach ($snmprec_data as $line) {
        if (!empty($line)) {
            list($oid, $type, $data) = explode('|', $line, 3);
            $result[$oid] = $line;
        }
    }

    return $result;
}


function compareOid($a, $b)
{
    $a_oid = explode('.', $a);
    $b_oid = explode('.', $b);

    foreach ($a_oid as $index => $a_part) {
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
