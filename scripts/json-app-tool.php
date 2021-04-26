#!/usr/bin/env php
<?php

// Pulled from includes/polling/functions.inc.php
function data_flatten($array, $prefix = '', $joiner = '_')
{
    $return = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (strcmp($prefix, '')) {
                $key = $prefix . $joiner . $key;
            }
            $return = array_merge($return, data_flatten($value, $key, $joiner));
        } else {
            if (strcmp($prefix, '')) {
                $key = $prefix . $joiner . $key;
            }
            $return[$key] = $value;
        }
    }

    return $return;
}

// Pulled from includes/snmp.inc.php
function string_to_oid($string)
{
    $oid = strlen($string);
    for ($i = 0; $i != strlen($string); $i++) {
        $oid .= '.' . ord($string[$i]);
    }

    return $oid;
}//end string_to_oid()

// Options!
$short_opts = 'sktmlhj:a:';
$options = getopt($short_opts);

// print the help
if (isset($options['h'])) {
    echo 'LibreNMS JSON App tool
  -j      The file containing the JSON to use for the test.
  -s      Print the SNMPrec data.
  -t      Print the JSON test data file.
  -l      Just load and lint the JSON file.
  -m      Extract and print metric variables from the JSON file.
  -k      If m is specified, just print the keys in tested order.
  -a      The application name for use with -s and -t.
  -h      Show this help text.

-j must always be specified.

-a must always be given with -s and -t.

-l if specified will override any others. If none of the others are
   specified and just -j is given, then the file is loaded and then
   linted if needed. For linting jsonlint needs to be installed.

-m is handy if you want to grab a list of metrics a JSON app is returning
   and how they data is flattned as well for when writing alert rules. -k
   prints it in a slightly neater manner and in a manner and in tested
   order.
';
    exit();
}

// make sure we have a JSON file to work with
if (! isset($options['j'])) {
    echo "Nothing JSON file specified via -j.\n";
    exit(1);
}

//read in the file
$raw_json = file_get_contents($options['j']);
if ($raw_json === false) {
    exit(2);
}

// parse the read file
$json = json_decode(stripslashes($raw_json), true);

// check json_decode() for any errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Parsing '" . $options['j'] . "' failed. Running jsonlint...\n\n";
    system('jsonlint ' . escapeshellarg($options['j']));
    exit(3);
}

//make sure the JSON actually contains something
if (empty($json)) {
    echo "'" . $options['j'] . "' is a blank JSON file.\n";
    exit(4);
}

//make sure it has all the required keys
if (! isset($json['error'], $json['data'], $json['errorString'], $json['version'])) {
    echo "'" . $options['j'] . "' is missing one or more of the keys 'error', 'errorString', 'version', or 'data'.\n";
    exit(5);
}

//successfully loaded and tested the file, just exit now if asked to
if ((isset($options['l'])) || (
    (! isset($options['t'])) &&
    (! isset($options['s'])) &&
    (! isset($options['m']))
    )) {
    exit(0);
}

//pulls out the metrics
$data = $json['data'];
$metrics = data_flatten($data);
$metrics_keys = array_keys($metrics);
usort($metrics_keys, 'strcasecmp'); //orders them in the manner in which the test script compares them
//print metrics if needed
if (isset($options['m'])) {
    if (isset($options['k'])) {
        foreach ($metrics_keys as $key) {
            echo $key . "\n";
        }
    } else {
        foreach ($metrics_keys as $key) {
            echo $key . '=' . $metrics[$key] . "\n";
        }
    }
    exit(0);
}

// exit if -s or -t is not requested
if ((! isset($options['s'])) && (! isset($options['t']))) {
    exit(0);
}

// For anything past here, we need -a given
if (! isset($options['a'])) {
    echo "Nothing specified via -a\n";
    exit(1);
}

// Output snmprec data for snmpsim for use with testing.
if (isset($options['s'])) {
    $oid = string_to_oid($options['a']);
    echo "1.3.6.1.2.1.1.1.0|4|Linux server 3.10.0-693.5.2.el7.x86_64 #1 SMP Fri Oct 20 20:32:50 UTC 2017 x86_64\n" .
        "1.3.6.1.2.1.1.2.0|6|1.3.6.1.4.1.8072.3.2.10\n" .
        "1.3.6.1.2.1.1.3.0|67|77550514\n" .
        "1.3.6.1.2.1.1.4.0|4|<private>\n" .
        "1.3.6.1.2.1.1.5.0|4|<private>\n" .
        "1.3.6.1.2.1.1.6.0|4|<private>\n" .
        "1.3.6.1.2.1.25.1.1.0|67|77552962\n" .
        "1.3.6.1.4.1.8072.1.3.2.2.1.21.6.100.105.115.116.114.111|2|1\n" .
        '1.3.6.1.4.1.8072.1.3.2.2.1.21.' . $oid . "|2|1\n" .
        '1.3.6.1.4.1.8072.1.3.2.3.1.2.' . $oid . '|4x|' . bin2hex($raw_json) . "\n";
    exit(0);
}

// prints the json test data file if asked to
if (isset($options['t'])) {
    $test_data = [
        'applications' => [
            'discovery' => [
                'applications' => [
                    'app_type' => $options['a'],
                    'app_state' => 'UNKNOWN',
                    'discovered' => '1',
                    'app_state_prev' => null,
                    'app_status' => '',
                    'app_instance' => '',
                ],
                'application_metrics' => [],
            ],
            'poller' => [
                'applications' => [
                    'app_type' => $options['a'],
                    'app_state' => 'OK',
                    'discovered' => '1',
                    'app_state_prev' => 'UNKNOWN',
                    'app_status' => '',
                    'app_instance' => '',
                ],
                'application_metrics' => [],
            ],
        ],
    ];
    foreach ($metrics_keys as $key) {
        $test_data['applications']['poller']['application_metrics'][] = [
            'metric' => $key,
            'value' => $metrics[$key],
            'value_prev' => null,
            'app_type' => $options['a'],
        ];
    }
    echo json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
    exit(0);
}
