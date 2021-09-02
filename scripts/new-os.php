#!/usr/bin/env php
<?php

use LibreNMS\Config;
use LibreNMS\Modules\Core;
use LibreNMS\Util\Debug;

$init_modules = [''];
require __DIR__ . '/../includes/init.php';

$options = getopt('h:o:t:v:d::');

if ($options['h'] && $options['o'] && $options['t'] && $options['v']) {
    $type = $options['t'];
    $vendor = $options['v'];
    Debug::set(isset($options['d']));

    $device_id = ctype_digit($options['h']) ? $options['h'] : getidbyname($options['h']);
    $device = device_by_id_cache($device_id);
    $definition_file = Config::get('install_dir') . "/includes/definitions/{$options['o']}.yaml";
    $discovery_file = Config::get('install_dir') . "/includes/definitions/discovery/{$options['o']}.yaml";
    $test_file = Config::get('install_dir') . "/tests/snmpsim/{$options['o']}.snmprec";
    if (file_exists($definition_file)) {
        c_echo("The OS {$options['o']} appears to exist already, skipping to sensors support\n");
    } else {
        $sysDescr = snmp_get($device, 'sysDescr.0', '-OvQ', 'SNMPv2-MIB');
        $sysObjectID = explode('.', ltrim(snmp_get($device, 'sysObjectID.0', '-OnvQ', 'SNMPv2-MIB'), '.'));
        $end_oid = array_pop($sysObjectID);
        $sysObjectID = '.' . implode('.', $sysObjectID);
        $full_sysObjectID = "$sysObjectID.$end_oid";

        c_echo("
sysDescr: $sysDescr
sysObjectID: $full_sysObjectID

");

        $os = Core::detectOS($device);
        $continue = 'n';
        if ($os != 'generic') {
            $continue = get_user_input("We already detect this device as OS $os type, do you want to continue to add sensors? (Y/n)");
        }

        if (! str_i_contains($continue, 'y')) {
            $descr = get_user_input('Enter the description for this OS, i.e Cisco IOS:');
            $icon = get_user_input('Enter the logo to use, this can be the name of an existing one (i.e: cisco) or the url to retrieve one:');

            if (filter_var($icon, FILTER_VALIDATE_URL)) {
                $icon_data = file_get_contents($icon);
                file_put_contents(Config::get('temp_dir') . "/{$options['o']}", $icon_data);
                $file_info = mime_content_type(Config::get('temp_dir') . "/{$options['o']}");
                if ($file_info === 'image/png') {
                    $ext = '.png';
                } elseif ($file_info === 'image/svg+xml') {
                    $ext = '.svg';
                }
                rename(Config::get('temp_dir') . "/{$options['o']}", Config::get('install_dir') . "/html/images/os/$vendor$ext");
                $icon = $vendor;
            }

            $disco = "os: {$options['o']}
text: '$descr'
type: $type
icon: $icon
group: $vendor
over:
    - { graph: device_bits, text: 'Device Traffic' }
    - { graph: device_processor, text: 'CPU Usage' }
    - { graph: device_mempool, text: 'Memory Usage' }
discovery:
    - sysObjectID:
        - $sysObjectID
";
            file_put_contents($definition_file, $disco);

            $snmprec = "1.3.6.1.2.1.1.1.0|4|$sysDescr
1.3.6.1.2.1.1.2.0|6|$full_sysObjectID
";

            file_put_contents($test_file, $snmprec);
        }

        if ($os === 'generic') {
            c_echo('Base discovery file created,');
        }
    }

    $mib_name = get_user_input('ctrl+c to exit now otherwise please enter the MIB name including path (url is also fine) for us to check for sensors:');

    if (filter_var($mib_name, FILTER_VALIDATE_URL)) {
        $mib_data = file_get_contents($mib_name);
        file_put_contents(Config::get('temp_dir') . "/{$options['o']}.mib", $mib_data);
        $file_info = mime_content_type(Config::get('temp_dir') . "/{$options['o']}.mib");
        if ($file_info !== 'text/plain') {
            c_echo("That mib file isn't a plain text file and is instead $file_info so we aren't using it");
            exit(1);
        }
        preg_match('/(.* DEFINITIONS ::)/', $mib_data, $matches);
        [$mib_name,] = explode(' ', $matches[0], 2);
        if (file_exists(Config::get('install_dir') . "/mibs/$vendor/") == false) {
            mkdir(Config::get('install_dir') . "/mibs/$vendor/");
        }
        rename(Config::get('temp_dir') . "/{$options['o']}.mib", Config::get('install_dir') . "/mibs/$vendor/$mib_name");
    } elseif ($mib_name) {
        $tmp_mib = explode('/', $mib_name);
        $mib_name = array_pop($tmp_mib);
    }

    $translate_cmd = Config::get('snmptranslate') . ' -M ' . Config::get('mib_dir') . ':' . Config::get('mib_dir') . "/$vendor -m $mib_name -TB '.*Table$' -Os";
    $tables = shell_exec($translate_cmd);
    foreach (explode(PHP_EOL, $tables) as $table_name) {
        if ($table_name) {
            $continue = get_user_input("Do you want to add $table_name? (y/N)");
            if ($continue === 'y' || $continue === 'Y') {
                $mib2c_cmd = 'env MIBDIRS=' . Config::get('mib_dir') . ':' . Config::get('mib_dir') . "/$vendor/ env MIBS=\"$mib_name\" mib2c -q -c misc/mib2c.conf $table_name";
                $tmp_info = shell_exec($mib2c_cmd);
                $table_info = Symfony\Component\Yaml\Yaml::parse($tmp_info);
                $type = get_user_input('Enter the sensor type, i.e temperature, voltage, etc:');
                echo 'Table info:' . PHP_EOL;
                foreach ($table_info['data'] as $data) {
                    echo $data['name'] . PHP_EOL;
                    $tmp_table[$data['name']] = $data['oid'];
                }
                $value = get_user_input('Enter value:');
                $descr = get_user_input('Enter descr:');
                $divisor = get_user_input('Enter divisor (defaults to 1)');
                $multiplier = get_user_input('Enter multiplier (defaults to 1)');
                if ($type && $value && $descr) {
                    $discovery[$type] .= "
                -
                    oid: $table_name
                    value: $value
                    num_oid: '{$tmp_table[$value]}.{{ \$index }}'
                    descr: $descr";
                    if ($multiplier) {
                        $discovery[$type] .= "\n                    multiplier: $multiplier";
                    }
                    if ($divisor) {
                        $discovery[$type] .= "\n                    divisor: $divisor";
                    }
                }
            }
        }
    }

    if (is_array($discovery)) {
        $discovery_data = "mib: $mib_name
modules:
    sensors:";
        foreach ($discovery as $sensor => $sensor_data) {
            $discovery_data .= "
        $sensor:
            data:$sensor_data";
        }
    }

    if (file_exists($discovery_file) === false) {
        if (file_put_contents($discovery_file, $discovery_data)) {
            c_echo("New discovery file $discovery_file has been created");
        } else {
            c_echo("Failed to create new discovery file $discovery_file");
        }
    } else {
        c_echo("$discovery_file already exists, here's the data we would have added:");
        c_echo($discovery_data);
    }
} else {
    c_echo('
Info:
    You can use to build the yaml files for a new OS.
Usage:
    -h Is the device ID or hostname of the device in LibreNMS detected as generic
    -o This is the OS name, i.e ios, nxos, eos
    -t This is the OS type, i.e network, power, etc
    -v The vendor name in lower case, i.e cisco, arista

Example:
./scripts/new-os.php -h 44 -o new-eos

');
    exit(1);
}

function get_user_input($msg)
{
    c_echo($msg . ' ');
    $handle = fopen('php://stdin', 'r');
    $line = fgets($handle);

    return trim($line);
}
