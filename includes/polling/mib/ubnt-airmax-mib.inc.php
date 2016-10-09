<?php

/*
 * @copyright  (C) 2015 Mark Gibbons
 */

// Polling of Airmax MIB AP for Ubiquiti Airmax Radios
//
// UBNT-AirMAX-MIB
echo ' UBNT-AirMAX-MIB ';

// Check If It Is A Device that supports latest Airmax MIB By Trying To Read Frequency
// if (substr($device['version'],0,3) == "5.6")
if (is_numeric(snmp_get($device, 'ubntRadioFreq.1', '-OUqnv', 'UBNT-AirMAX-MIB'))) {
    // $mib_oids                              (oidindex,dsname,dsdescription,dstype)
    $mib_oids = array(
        'ubntRadioFreq'        => array(
            '1',
            'RadioFreq',
            'Frequency',
            'GAUGE',
        ),
        'ubntRadioTxPower'     => array(
            '1',
            'RadioTxPower',
            'Tx Power',
            'GAUGE',
        ),
        'ubntRadioDistance'    => array(
            '1',
            'RadioDistance',
            'Distance',
            'GAUGE',
        ),
        'ubntRadioRssi.1.1'    => array(
            '',
            'RadioRssi_0',
            'RSSI Chain 0',
            'GAUGE',
        ),
        'ubntRadioRssi.1.2'    => array(
            '',
            'RadioRssi_1',
            'RSSI Chain 1',
            'GAUGE',
        ),
        'ubntWlStatSignal'     => array(
            '1',
            'WlStatSignal',
            'Signal',
            'GAUGE',
        ),
        'ubntWlStatRssi'       => array(
            '1',
            'WlStatRssi',
            'Overall RSSI',
            'GAUGE',
        ),
        'ubntWlStatCcq'        => array(
            '1',
            'WlStatCcq',
            'Transmit CCQ',
            'GAUGE',
        ),
        'ubntWlStatNoiseFloor' => array(
            '1',
            'WlStatNoiseFloor',
            'Noise Floor',
            'GAUGE',
        ),
        'ubntWlStatTxRate'     => array(
            '1',
            'WlStatTxRate',
            'Tx Rate',
            'GAUGE',
        ),
        'ubntWlStatRxRate'     => array(
            '1',
            'WlStatRxRate',
            'Rx Rate',
            'GAUGE',
        ),
        'ubntWlStatStaCount'   => array(
            '1',
            'WlStatStaCount',
            'Sta Count',
            'GAUGE',
        ),
        'ubntAirMaxQuality'    => array(
            '1',
            'AirMaxQuality',
            'AirMax Quality',
            'GAUGE',
        ),
        'ubntAirMaxCapacity'   => array(
            '1',
            'AirMaxCapacity',
            'AirMax Capacity',
            'GAUGE',
        ),
    );

    $mib_graphs = array();

    // Build Graph List Array
    if (1 == 1) {
        // Is It An AP
        if (stristr(snmp_get($device, 'ubntRadioMode.1', '-OUqnv', 'UBNT-AirMAX-MIB'), 'ap')) {
            // Yes - Add Station Count Graph
            array_push($mib_graphs, 'ubnt_airmax_WlStatStaCount');
        }

        // Add Common Graphs
        array_push(
            $mib_graphs,
            'ubnt_airmax_RadioFreq',
            'ubnt_airmax_RadioTxPower',
            'ubnt_airmax_RadioDistance',
            'ubnt_airmax_RadioRssi_0',
            'ubnt_airmax_RadioRssi_1',
            'ubnt_airmax_WlStatSignal',
            'ubnt_airmax_WlStatRssi',
            'ubnt_airmax_WlStatCcq',
            'ubnt_airmax_WlStatNoiseFloor',
            'ubnt_airmax_WlStatTxRate',
            'ubnt_airmax_WlStatRxRate'
        );
        // Is Airmax Enabled?
        if (snmp_get($device, 'ubntAirMaxEnabled.1', '-OUqnv', 'UBNT-AirMAX-MIB') == 'true') {
            // Check To See If It Is An AC Device - Returns Airmax Capacity of 0
            if (snmp_get($device, 'ubntAirMaxCapacity.1', '-OUqnv', 'UBNT-AirMAX-MIB') != 0) {
                // No - Not AC - add AirMax Graphs
                array_push($mib_graphs, 'ubnt_airmax_AirMaxQuality', 'ubnt_airmax_AirMaxCapacity');
            }
        }
    }//end if

    unset($graph, $oids, $oid);
    poll_mib_def($device, 'UBNT-AirMAX-MIB:UBNT', 'ubiquiti', $mib_oids, $mib_graphs, $graphs);
}//end if
