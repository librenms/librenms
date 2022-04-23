<?php

use LibreNMS\RRD\RrdDefinition;

$rrd_name = Rrd::portName($port_id, 'poe');
$rrd_def = RrdDefinition::make()
    ->addDataset('PortPwrAllocated', 'GAUGE', 0)
    ->addDataset('PortPwrAvailable', 'GAUGE', 0)
    ->addDataset('PortConsumption', 'GAUGE', 0)
    ->addDataset('PortMaxPwrDrawn', 'GAUGE', 0);

if (($device['os'] == 'vrp')) {
    //Tested against Huawei 5720 access switches
    if (isset($this_port['hwPoePortEnable'])) {
        $upd = "$polled:" . $this_port['hwPoePortReferencePower'] . ':' . $this_port['hwPoePortMaximumPower'] . ':' . $this_port['hwPoePortConsumingPower'] . ':' . $this_port['hwPoePortPeakPower'];

        $fields = [
            'PortPwrAllocated'   => $this_port['hwPoePortReferencePower'],
            'PortPwrAvailable'   => $this_port['hwPoePortMaximumPower'],
            'PortConsumption'    => $this_port['hwPoePortConsumingPower'],
            'PortMaxPwrDrawn'    => $this_port['hwPoePortPeakPower'],
        ];

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(vrp) ';
    }
} elseif (($device['os'] == 'linksys-ss')) {
    //Tested 318P
    if (isset($this_port['pethPsePortAdminEnable'])) {
        $upd = "$polled:" . $this_port['rlPethPsePortPowerLimit'] . ':' . $this_port['rlPethPsePortOutputPower'];

        $fields = [
            'PortPwrAllocated'   => $this_port['rlPethPsePortPowerLimit'],
            'PortPwrAvailable'   => $this_port['rlPethPsePortPowerLimit'],
            'PortConsumption'    => $this_port['rlPethPsePortOutputPower'],
            'PortMaxPwrDrawn'    => $this_port['rlPethPsePortPowerLimit'],
        ];

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(linksys) ';
    }
} elseif (($device['os'] == 'ios') || ($device['os'] == 'iosxe')) {
    // Code for Cisco IOS and IOSXE, tested on 2960X
    if (isset($this_port['cpeExtPsePortPwrAllocated'])) {
        // if we have cpeExtPsePortPwrAllocated, we have the complete array so we can populate the RRD
        $upd = "$polled:" . $port['cpeExtPsePortPwrAllocated'] . ':' . $port['cpeExtPsePortPwrAvailable'] . ':' .
            $port['cpeExtPsePortPwrConsumption'] . ':' . $port['cpeExtPsePortMaxPwrDrawn'];
        echo "$this_port[cpeExtPsePortPwrAllocated],$this_port[cpeExtPsePortPwrAvailable],$this_port[cpeExtPsePortPwrConsumption],$this_port[cpeExtPsePortMaxPwrDrawn]\n";
        $fields = [
            'PortPwrAllocated'   => $this_port['cpeExtPsePortPwrAllocated'],
            'PortPwrAvailable'   => $this_port['cpeExtPsePortPwrAvailable'],
            'PortConsumption'    => $this_port['cpeExtPsePortPwrConsumption'],
            'PortMaxPwrDrawn'    => $this_port['cpeExtPsePortMaxPwrDrawn'],
        ];

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(IOS) ';
    }//end if
} elseif (($device['os'] == 'jetstream')) {
    if (isset($this_port['tpPoePortStatus'])) {
        // TP-Link uses .1W for their units; convert to milliwatts.
        $fields = [
            'PortPwrAllocated'   => $this_port['tpPoePowerLimit'] * 100,
            'PortPwrAvailable'   => $this_port['tpPoePowerLimit'] * 100,
            'PortConsumption'    => $this_port['tpPoePower'] * 100,
            'PortMaxPwrDrawn'    => $this_port['tpPoePowerLimit'] * 100,
        ];

        $tags = compact('ifName', 'rrd_name', 'rrd_def');
        data_update($device, 'poe', $tags, $fields);
        echo 'PoE(jetstream) ';
    }
}
