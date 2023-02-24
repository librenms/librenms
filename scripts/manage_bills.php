#!/usr/bin/env php
<?php

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

/** Bill management tool
 * Todo:
 * - Actually create a bill
 * - Option to empty a bill
 * - Probably tons of bug fixes and safety checks.
 * Note:
 * - Current, this cannot create a new bill. To do this, you need to use the GUI.
 **/

// Find the correct bill, exit if we get anything other than 1 result.
function list_bills($bill_name)
{
    $bill = dbFetchRows('SELECT `bill_id`,`bill_name` FROM `bills` WHERE `bill_name` LIKE ?', ["$bill_name"]);
    if (count($bill) != 1) {
        echo "Did not find exactly 1 bill, exiting\n";
        echo 'Query:' . $bill . "\n";
        exit(1);
    } else {
        echo "Found bill {$bill[0]['bill_name']} ({$bill[0]['bill_id']})\n";
    }

    return $bill[0]['bill_id'];
}

// Create a new bill.
function create_bill($bill_name, $bill_type, $bill_cdr, $bill_day)
{
    /** create_bill
     * Note:
     * - bill_name: can be a duplicate since it's unique is is bill_id. We are going to be cowards and refuse to create duplicate bill_name
     * - bill_type: can be cdr (Committed data rate, 95th) or quota (total bytes moved)
     * - bill_cdr:  if bill_type is cdr, then this is in bits, if bill_type is quota, then it's in bytes (!!)
     * - bill_day:  day of month billing starts.
     **/
    echo 'Creating bill with name : ' . $bill_name . ' (Type: ' . $bill_type . ', Quota: ' . $bill_cdr . ")\n";
    $insert = [
        'bill_name' => $bill_name,
        'bill_type' => $bill_type,
        'bill_cdr' =>  $bill_cdr,
        'bill_day' => '1',
    ];
    $create_bill = dbInsert($insert, 'bills');
    echo 'Created bill ID ' . $create_bill . "\n";

    return $create_bill;
}

// This will get an array of devices we are interested in from the CLI glob
function get_devices($host_glob, $nameType)
{
    return dbFetchRows('SELECT `device_id`,`hostname`,`sysName` FROM `devices` WHERE `' . $nameType . '` LIKE ?', ["%$host_glob%"]);
}

// This will flush bill ports if -r is set on cli
function flush_bill($id)
{
    echo "Removing ports from bill ID $id\n";

    return dbDelete('bill_ports', '`bill_id` = ?', [$id]);
}

function add_ports_to_bill($devs, $intf_glob, $id)
{
    // Abort mission if no bill id is passed.
    if (empty($id)) {
        echo "No bill ID passed, exiting...\n";
        exit(1);
    }

    // Expected interface glob:
    echo "Interface glob: $intf_glob\n";
    $device_ids = array_column($devs, 'device_id');
    $ids = implode(',', $device_ids);

    // Find the devices which match the list of IDS and also the interface glob
    $query = "SELECT ports.port_id,ports.ifName,ports.ifAlias FROM ports INNER JOIN devices ON ports.device_id = devices.device_id WHERE ifType = 'ethernetCsmacd' AND ports.ifAlias LIKE '%$intf_glob%' AND ports.device_id in ($ids)";
    echo "Query: $query\n";
    foreach (dbFetchRows($query) as $ports) {
        echo "Inserting {$ports['ifName']} ({$ports['ifAlias']}) into bill $id\n";
        $insert = [
            'bill_id' => $id,
            'port_id' => $ports['port_id'],
            'bill_port_autoadded' => '1',
        ];
        dbInsert($insert, 'bill_ports');
    }

    return true;
}

function print_help()
{
    echo "Usage:\n";
    echo "Updating bills\n";
    echo "-b <bill name glob>   Bill name to match\n";
    echo "-s <sysName glob>     sysName to match (Cannot be used with -h)\n";
    echo "-h <hostname glob>    Hostname to match (Cannot be used with -s)\n";
    echo "-i <Interface description glob>   Interface description to match\n";
    echo "-f Flush all ports from a bill before adding adding ports\n";
    echo "Creating bills\n";
    echo "-n Create new bill\n";
    echo "-t bill type (cdr or quota)\n";
    echo "-q Quota (In bits for cdr, bytes for quota)\n\n";
    echo "Update an existing bill called 'Telia - Transit', add interfaces matching \"Telia\" from all devices\n";
    echo "php manage_bills.php -b 'Telia - Transit' -s all -i Telia\n\n";
    echo "Create a new bill called 'Transit' with a CDR of 1Gbit\n";
    echo "php manage_bills.php -n -b 'Transit' -t cdr -q 1000000000";
    echo "\n";
    exit;
}

/** Setup options:
 * b - bill_name - bill glob
 * i - circuit_id - interface glob
 * s - sysName - device glob
 * h - hostname - device glob
 * f - flush - boolean
 * n - new - create new bill
 * t - type - bill type
 * q - quota - bill quota
 **/
$options = getopt('b:s:h:i:f:np:t:q:');

if (! empty($options['s'])) {
    $host_glob = str_replace('*', '%', $options['s']);
    $nameType = 'sysName';
}
if (! empty($options['h'])) {
    $host_glob = str_replace('*', '%', $options['h']);
    $nameType = 'hostname';
}
if (array_key_exists('n', $options)) {
    $create_bill = true;
}
if (! empty($options['t'])) {
    $bill_type = $options['t'];
}
if (! empty($options['q'])) {
    $bill_cdr = $options['q'];
}

$bill_name = str_replace('*', '%', $options['b']);
$intf_glob = str_replace('*', '%', $options['i']);

// Exit if no bill
if (empty($bill_name)) {
    echo "Please set -b (bill name)\n";
    print_help();
}
if ($create_bill) {
    create_bill($bill_name, $bill_type, $bill_cdr, '1');
    exit(1);
}
// Exit if missing hostname or sysName (or both set
if (empty($options['s']) && empty($options['h'])) {
    echo "Please set -s (sysName) or -h (hosthame)\n";
    print_help();
} elseif (! empty($options['s']) && ! empty($options['h'])) {
    echo "Please set either -s or -h, not both\n";
    print_help();
}
// Exit if missing hostname or sysName
if (empty($options['i'])) {
    echo "Please set -i (interface glob)\n";
    print_help();
}

if ($bill_name == 'all') {
    $bill_name = '%';
}
if ($intf_glob == 'all') {
    $intf_glob = '%';
}
if ($host_glob == 'all') {
    $host_glob = '%';
}
if (isset($options['f'])) {
    $flush = true;
} else {
    $flush = false;
}

$id = list_bills($bill_name);

$devices = get_devices($host_glob, $nameType);

if (empty($devices)) {
    echo "No devices found\n";
    exit(1);
}

if ($flush) {
    $flush_ret = flush_bill($id);
}

$ret = add_ports_to_bill($devices, $intf_glob, $id);
