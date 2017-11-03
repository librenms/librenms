#!/usr/bin/env php
<?php

$init_modules = array();
require realpath(__DIR__ . '/..') . '/includes/init.php';


## Find the correct bill, exit if we get anything other than 1 result. 
function list_bills($bill_name)
{
    $bill = dbFetchRows("SELECT `bill_id`,`bill_name` FROM `bills` WHERE `bill_name` LIKE ?", array('%'.$bill_name.'%'));
    $res = count($bill);
    if ($res != 1) {
        echo("Did not find exactly 1 bill, exiting\n");
        exit(1);
    }
    if ($res == 1) {
        echo("Found bill " . $bill[0]['bill_name'] . " (" . $bill[0]['bill_id'] . ")\n");
    }
    return $bill[0]['bill_id'];
}

# This will get an array of devices we are interested in from the CLI glob
function get_devices($host_glob)
{
    $devices = dbFetchRows("SELECT `device_id`,`hostname`,`sysName` FROM `devices` WHERE `sysName` LIKE ?", array('%'.$host_glob.'%'));
    #foreach ($devices as $device) {
    #    echo "Found device " . $device['sysName'] . "(hostname:" . $device['hostname'] . ")\n";
    #    }
    return $devices;
}


function create_bill($devs, $intf_glob, $id)
{
    # Abort mission if no bill id is passed.
    if (empty($id)) {
        echo ("No bill ID passed, exiting...\n");
        exit(1);
    }

    # Empty the existing bill since we dont want to duplicate ports.
    echo("Removing ports from bill ID ".$id."\n");
    dbDelete('bill_ports', '`bill_id` = ?', array($id));
    # Expected interface glob:
    echo("Interface glob: " . $intf_glob . "\n");
    # Devices IDS exploded to string
    $device_ids = array_column($devs, "device_id");
    $ids = implode(",", $device_ids);

    # Find the devices which match the list of IDS and also the interface glob
    $query = "SELECT ports.port_id,ports.ifName,ports.ifAlias FROM ports INNER JOIN devices ON ports.device_id = devices.device_id WHERE ifType = 'ethernetCsmacd' AND ports.ifAlias LIKE '%".$intf_glob."%' AND ports.device_id in (".$ids.")";
    echo("Query: " .$query . "\n");
    foreach (dbFetchRows($query) as $ports) {
        echo("Inserting ".$ports['ifName']." (" . $ports['ifAlias'] . " ) into bill ".$id."\n");
        $insert = array (
            'bill_id' => $id,
            'port_id' => $ports['port_id'],
            'bill_port_autoadded' => '1'
        );
        ## insert
        dbInsert($insert, 'bill_ports');
    }
    return false;
}

## Bill management tool
## Todo: 
##   - Actually create a bill
##   - Option to empty a bill
##   - Merge a bill
##   - Probably tons of bug fixes and safety checks. 
## Note:
##   - Current, this cannot create a new bill. To do this, you need to use the GUI. 


## Setup options:
# l - bill_name - bill glob
# c circuit_id - interface glob
# d device_id - device glob
$options = getopt('l:c:d:');

# Replace "*" with SQL wildcard %. 

$bill_name = str_replace('*', '%', mres($options['l']));
$intf_glob = str_replace('*', '%', mres($options['c']));
$host_glob = str_replace('*', '%', mres($options['d']));


if (empty($bill_name)) {
    echo "Usage:\n";
    echo "-l <bill name glob>    Bill name to match\n";
    echo "-d <hostname glob>    Hostname to match\n";
    echo "-c <Interface description glob>    Interface description to match\n";
    echo "Example:\n";
    echo "If I wanted to add all interfaces containing the description Telia to a bill called 'My Lovely Transit Provider'\n";
    echo "php manage_bills.php -l 'My Lovely Transit Provider' -d all -c Telia";
    echo "\n";
    exit;
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

$devices = get_devices($host_glob);

$id = list_bills($bill_name);
$ret = create_bill($devices, $intf_glob, $id);
