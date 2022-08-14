<?php

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

//array (
//  9 =>
//  array (
//    'xtuc' =>
//    array (
//      'xdsl2ChStatusActDataRate' => '147681',
//    ),
//    'xtur' =>
//    array (
//      'xdsl2ChStatusActDataRate' => '51547',
//    ),
//    'xdsl2LineStatusAttainableRateDs' => '153868',
//    'xdsl2LineStatusAttainableRateUs' => '51824',
//  ),
//)

if (isset($this_port['xtuc'])) {
    d_echo ($this_port);

    $multiplier = 1;

    if ($device['os'] == 'vrp') {
        $multiplier = 1024;
    }

    $rrd_att_name = Rrd::portName($port_id, 'xdsl2LineStatusAttainableRate');
    $rrd_att_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('Ds', 'GAUGE', 0, 99999999)
        ->addDataset('Us', 'GAUGE', 0, 99999999);
//    'xdsl2LineStatusAttainableRateDs' => '153868',
//    'xdsl2LineStatusAttainableRateUs' => '51824',

    $rrd_act_name = Rrd::portName($port_id, 'xdsl2LineStatusActRate');
    $rrd_act_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('Xtuc', 'GAUGE', 0, 99999999)
        ->addDataset('Xtur', 'GAUGE', 0, 99999999);
//  array (
//    'xtuc' =>
//    array (
//      'xdsl2ChStatusActDataRate' => '147681',
//    ),
//    'xtur' =>
//    array (
//      'xdsl2ChStatusActDataRate' => '51547',
//    ),

// NO DB for now

//    if (dbFetchCell('SELECT COUNT(*) FROM `ports_adsl` WHERE `port_id` = ?', [$port_id]) == '0') {
//        dbInsert(['port_id' => $port_id], 'ports_adsl');
//    }

//    $port['adsl_update'] = ['port_adsl_updated' => ['NOW()']];
//    foreach ($adsl_db_oids as $oid) {
//        $port['adsl_update'][$oid] = $data;
//    }

//    dbUpdate($port['adsl_update'], 'ports_adsl', '`port_id` = ?', [$port_id]);


    $fields = [];
//    foreach ($adsl_oids as $oid) {
//        $oid = 'adsl' . $oid;
//        $data = str_replace('"', '', $this_port[$oid]);
//        // Set data to be "unknown" if it's garbled, unexistant or zero
//        if (! is_numeric($data)) {
//            $data = 'U';
//        }
//        $fields[$oid] = $data;
//    }

//    $tags = compact('ifName', 'rrd_name', 'rrd_def');
//    data_update($device, 'adsl', $tags, $fields);

    echo 'VDSL (' . $this_port['ifName'] . '/' . Number::formatSi($this_port['xtur']['xdsl2ChStatusActDataRate']*$multiplier, 2, 3, 'bps') . '/' . Number::formatSi($this_port['xtuc']['xdsl2ChStatusActDataRate']*$multiplier, 2, 3, 'bps') . ') \n';

}//end if
