<?php


/* foreach (snmpwalk_group($device, 'fxsPortStatusStatus', 'L-AM3440-A-Private') as $index => $fxs_port) {
    #print_r($fxo_port);
    $dex = $dex + 1;
    #print_r($fxs_port['fxsPortStatusItemIndex']);#  INTEGER {off-hook(1), metering-pulse(2), tip-open(3), ring-gnd(4), plar-on(5), ringing(6),alarm-on(7)
    #print_r($fxs_port['fxsPortStatusPortIndex']);# Port number
    #print_r($fxs_port['fxsPortStatusIndex']);# Slot number
    print_r
    
    $index_brocade = $dex + 1073741823;
    $port_stats[$index_brocade]['ifAlias'] = $fxs_port['fxsPortStatusStatus'];
    $port_stats[$index_brocade]['ifDescr'] = $fxs_port['fxsPortStatusStatus'];
    $port_stats[$index_brocade]['ifName'] = $fxs_port['fxsPortStatusStatus'];
    break;
} */

$curIfIndex = 0;
$fxs_stats = snmpwalk_group($device, 'fxsPortStatusStatus', 'L-AM3440-A-Private'); 
$eth_stats = snmpwalk_group($device, 'ethernetStatusTable', 'L-AM3440-A-Private'); #Get eth status
$eth_traffic = snmpwalk_group($device, 'ethernetCountTable', 'L-AM3440-A-Private'); #Get eth traffic


//Add FXS ports
/* foreach ($fxs_stats as $index => $fsxcard_stats) {
    $curIfIndex = $curIfIndex + 1;
    print_r($fsxcard_stats['fxsPortStatusStatus']); # on/off

    //Loop over every card to find each port

        //Set port name
        //Set description 
        //Set up/down


    $port_stats[$curIfIndex]['ifDescr'] = 'test ';
    $port_stats[$curIfIndex]['ifName'] = "test $curIfIndex";
    $port_stats[$curIfIndex]['ifOperStatus'] = ($fsxport_stats['fxsPortStatusStatus']["slot-1"] == 1 ? 'up' : 'down');
    return;
} */


//Set eth interfaces
foreach ($eth_stats as $index => $port) {
    $curIfIndex = $curIfIndex + 1;
    
    $port_stats[$curIfIndex]['ifName'] = "int_".$port['ethernetStatusName'];
    $port_stats[$curIfIndex]['ifOperStatus'] = ($port['ethernetStatusLink'] == 1 ? 'up' : 'down');
    $port_stats[$curIfIndex]['ifDescr'] = "int_".$port['ethernetStatusName'];
    #$port_stats[$curIfIndex]['ifAlias'] = $port['ethernetStatusName'];

    switch ($port['ethernetStatusSpeed']) {
        case 1: #Port is in auto mode. We asume 1gbps
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            break;
        case 2: #1000mbps full duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            break;
        case 3:#1000mbps half duplex
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            $port_stats[$curIfIndex]['ifSpeed'] = 1000000000;
            break;
        case 4: #100mbps full duplex
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                break;
        case 5: #100mbps half duplex
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                break;
        case 6: #10mbps full duplex
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                break;
        case 7: #10mbps half duplex
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                $port_stats[$curIfIndex]['ifSpeed'] = 100000000;
                break;
    }

    
    if($port_stats[$curIfIndex]['ifOperStatus'] = 'up'){
        print_r('IS UP');
        $port_stats[$curIfIndex]['ifInOctets'] = $eth_traffic[$index]['ethernetRxLength'];
        $port_stats[$curIfIndex]['ifOutOctets'] = $port[$index]['ethernetTxLength'];
        $port_stats[$curIfIndex]['ifInErrors'] = $port[$index]['ethernetRxBadCount']; 
    
    }
}
