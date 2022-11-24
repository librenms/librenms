<?php

unset($port_stats); //Unsetting stats to prevent adding the interfaces found with the IF-MIB

$curIfIndex = 0;
//$fxs_stats = snmpwalk_group($device, 'fxsPortStatusStatus', 'L-AM3440-A-Private');
$eth_stats = snmpwalk_group($device, 'ethernetStatusTable', 'L-AM3440-A-Private'); //Get eth status
$eth_traffic = snmpwalk_group($device, 'ethernetCountTable', 'L-AM3440-A-Private'); //Get eth traffic


//Set eth interfaces
foreach ($eth_stats as $index => $port) {
    $curIfIndex = $curIfIndex + 1;
    $portname = snmp_hexstring($port['ethernetStatusName']); // Convert hex to readable string
    $port_stats[$curIfIndex]['ifName'] = $portname;
    $port_stats[$curIfIndex]['ifOperStatus'] = ($port['ethernetStatusLink'] == 1 ? 'up' : 'down');
    $port_stats[$curIfIndex]['ifDescr'] = $portname;
    $port_stats[$curIfIndex]['ifType'] = $port['ethernetStatusMode']; //Set mode copper
    //$port_stats[$curIfIndex]['ifAlias'] = $port['ethernetStatusName'];

    //Set interface speed
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
    //Set port mode
    switch ($port['ethernetStatusMode']) {
        case 1: //Copper
            $port_stats[$curIfIndex]['ifType'] = 'copper';

            break;
        case 2: //optical
            $port_stats[$curIfIndex]['ifType'] = 'optical';
            break;
        case 3: //none
            $port_stats[$curIfIndex]['ifType'] = 'none';
            break;
    }

    if ($port_stats[$curIfIndex]['ifOperStatus'] = 'up') {
        print_r('test test test ');
        print_r($eth_traffic[$index]['ethernetTxGoodPkt']);
        print_r($eth_traffic[$index]['ethernetRxGoodPkt']);
        print_r('stop');
        $port_stats[$curIfIndex]['ifInOctets'] = $eth_traffic[$index]['ethernetTxGoodPkt'];
        $port_stats[$curIfIndex]['ifOutOctets'] = $eth_traffic[$index][' ethernetRxGoodPkt'];
        $port_stats[$curIfIndex]['ifInErrors'] = $eth_traffic[$index]['ethernetRxBadCount'];
    }
}

unset($eth_stats);
unset($eth_traffic);
unset($curIfIndex);
