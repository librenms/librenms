<?php

use LibreNMS\RRD\RrdDefinition;

$rrd_name = getPortRrdName($port_id, 'poe');
$rrd_def = RrdDefinition::make()
    ->addDataset('PortPwrAllocated', 'GAUGE', 0)
    ->addDataset('PortPwrAvailable', 'GAUGE', 0)
    ->addDataset('PortConsumption', 'GAUGE', 0)
    ->addDataset('PortMaxPwrDrawn', 'GAUGE', 0);

if (($device['os'] == 'vrp')) {
    //Tested against Huawei 5720 access switches
    if (isset($this_port['hwPoePortEnable'])) {
        $upd = "$polled:".$this_port['hwPoePortReferencePower'].':'.$this_port['hwPoePortMaximumPower'].':'.$this_port['hwPoePortConsumingPower'].':'.$this_port['hwPoePortPeakPower'];

        $fields = array(
                'PortPwrAllocated'   => $this_port['hwPoePortReferencePower'],
                'PortPwrAvailable'   => $this_port['hwPoePortMaximumPower'],
                'PortConsumption'    => $this_port['hwPoePortConsumingPower'],
                'PortMaxPwrDrawn'    => $this_port['hwPoePortPeakPower'],
                   );

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(vrp) ';
    }
} elseif (($device['os'] == 'ios') || ($device['os'] == 'iosxe')) {
    // Code for Cisco IOS and IOSXE, tested on 2960X
    if (isset($this_port['cpeExtPsePortPwrAllocated'])) {
        // if we have cpeExtPsePortPwrAllocated, we have the complete array so we can populate the RRD
        $upd = "$polled:".$port['cpeExtPsePortPwrAllocated'].':'.$port['cpeExtPsePortPwrAvailable'].':'.
            $port['cpeExtPsePortPwrConsumption'].':'.$port['cpeExtPsePortMaxPwrDrawn'];
        echo "$this_port[cpeExtPsePortPwrAllocated],$this_port[cpeExtPsePortPwrAvailable],$this_port[cpeExtPsePortPwrConsumption],$this_port[cpeExtPsePortMaxPwrDrawn]\n";
        $fields = array(
                'PortPwrAllocated'   => $this_port['cpeExtPsePortPwrAllocated'],
                'PortPwrAvailable'   => $this_port['cpeExtPsePortPwrAvailable'],
                'PortConsumption'    => $this_port['cpeExtPsePortPwrConsumption'],
                'PortMaxPwrDrawn'    => $this_port['cpeExtPsePortMaxPwrDrawn'],
                   );

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(IOS) ';
    }//end if
} else {
    //This is the legacy code, to be tested against devices
    if ($this_port['dot3StatsIndex'] && $port['ifType'] == 'ethernetCsmacd') {
        $upd = "$polled:".$port['cpeExtPsePortPwrAllocated'].':'.$port['cpeExtPsePortPwrAvailable'].':'.
            $port['cpeExtPsePortPwrConsumption'].':'.$port['cpeExtPsePortMaxPwrDrawn'];

        $fields = array(
                'PortPwrAllocated'   => $port['cpeExtPsePortPwrAllocated'],
                'PortPwrAvailable'   => $port['cpeExtPsePortPwrAvailable'],
                'PortConsumption'    => $port['cpeExtPsePortPwrConsumption'],
                'PortMaxPwrDrawn'    => $port['cpeExtPsePortMaxPwrDrawn'],
                   );

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);

        echo 'PoE(generic) ';
    }//end if
}
