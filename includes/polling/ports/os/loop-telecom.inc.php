<?php

unset($port_stats); //Unsetting stats to prevent adding the interfaces found with the IF-MIB

$curIfIndex = 0;
$eth_stats = snmpwalk_group($device, 'ethernetStatusTable', 'L-AM3440-A-Private'); //Get eth status
$eth_traffic = snmpwalk_group($device, 'ethernetCountTable', 'L-AM3440-A-Private'); //Get eth traffic

//Set eth interfaces
foreach ($eth_stats as $index => $port) {
    $curIfIndex = $curIfIndex + 1;
    $portname = snmp_hexstring($port['ethernetStatusName']); // Convert hex to readable string
    $port_stats[$curIfIndex]['ifName'] = $portname;
    $port_stats[$curIfIndex]['ifOperStatus'] = ($port['ethernetStatusLink'] == 1) ? 'up' : 'down';
    $port_stats[$curIfIndex]['ifAdminStatus'] = ($port['ethernetStatusLink'] == 1) ? 'up' : 'down'; //Set this to same as operator stat since the mib does not have admin status
    $port_stats[$curIfIndex]['ifDescr'] = $portname;
    $port_stats[$curIfIndex]['ifType'] = 'ethernetCsmacd'; //Set mode to ethernet

    //Set interface speed and duplex type
    switch ($port['ethernetStatusSpeed']) {
        case 1: //Port is in auto mode. We asume 1gbps
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'fullDuplex';
            break;
        case 2: //1000mbps full duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'fullDuplex';
            break;
        case 3: //1000mbps half duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'halfDuplex';
            break;
        case 4: //100mbps full duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'fullDuplex';
            break;
        case 5: //100mbps half duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'halfDuplex';
            break;
        case 6: //10mbps full duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 10000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'fullDuplex';
            break;
        case 7: //10mbps half duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 10000000;
            $port_stats[$curIfIndex]['ifDuplex'] = 'halfDuplex';
            break;
    }

    //Loop over eth ports and match ports to get correct data. The SNMP port is not defined in the ethernetCountTable oid
    foreach ($eth_traffic as $key => $value) {
        $portCountername = snmp_hexstring($value['ethernetCountName']); // Convert hex to readable string
        if ($portname == $portCountername) {
            $port_stats[$curIfIndex]['ifInOctets'] = abs($value['ethernetRxGoodPkt']);
            $port_stats[$curIfIndex]['ifOutOctets'] = abs($value['ethernetTxGoodPkt']);
            $port_stats[$curIfIndex]['ifInErrors'] = abs($value['ethernetRxBadCount']);
        }
    }
}

unset($eth_stats);
unset($eth_traffic);
unset($curIfIndex);
