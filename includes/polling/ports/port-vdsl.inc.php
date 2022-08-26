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
    $vdsl_db_oids = [
        'xdsl2LineStatusAttainableRateDs',
        'xdsl2LineStatusAttainableRateUs',
    ];

    d_echo($this_port);

    $multiplier = 1;

    if ($device['os'] == 'vrp') {
        $multiplier = 1024;
    }
    $rrd_att_name = Rrd::portName($port_id, 'xdsl2LineStatusAttainableRate');
    $rrd_att_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('ds', 'GAUGE', 0)
        ->addDataset('us', 'GAUGE', 0);
//    'xdsl2LineStatusAttainableRateDs' => '153868',
//    'xdsl2LineStatusAttainableRateUs' => '51824',

    $rrd_act_name = Rrd::portName($port_id, 'xdsl2ChStatusActDataRate');
    $rrd_act_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('xtuc', 'GAUGE', 0)
        ->addDataset('xtur', 'GAUGE', 0);
    //  array (
    //    'xtuc' =>
    //    array (
    //      'xdsl2ChStatusActDataRate' => '147681',
    //    ),
    //    'xtur' =>
    //    array (
    //      'xdsl2ChStatusActDataRate' => '51547',
    //    ),

    if (dbFetchCell('SELECT COUNT(*) FROM `ports_vdsl` WHERE `port_id` = ?', [$port_id]) == '0') {
        dbInsert(['port_id' => $port_id], 'ports_vdsl');
    }

    $port['vdsl_update'] = ['port_vdsl_updated' => ['NOW()']];
    foreach ($vdsl_db_oids as $oid) {
        $port['vdsl_update'][$oid] = $this_port[$oid];
    }
    $port['vdsl_update']['xdsl2ChStatusActDataRateXtur'] = $this_port['xtur']['xdsl2ChStatusActDataRate'];
    $port['vdsl_update']['xdsl2ChStatusActDataRateXtuc'] = $this_port['xtuc']['xdsl2ChStatusActDataRate'];

    dbUpdate($port['vdsl_update'], 'ports_vdsl', '`port_id` = ?', [$port_id]);

    $fieldsAtt['ds'] = $this_port['xdsl2LineStatusAttainableRateDs'];
    $fieldsAtt['us'] = $this_port['xdsl2LineStatusAttainableRateUs'];

    $fieldsAct['xtuc'] = $this_port['xtuc']['xdsl2ChStatusActDataRate'];
    $fieldsAct['xtur'] = $this_port['xtur']['xdsl2ChStatusActDataRate'];

//    foreach ($adsl_oids as $oid) {
//        $oid = 'adsl' . $oid;
//        $data = str_replace('"', '', $this_port[$oid]);
//        // Set data to be "unknown" if it's garbled, unexistant or zero
//        if (! is_numeric($data)) {
//            $data = 'U';
//        }
//        $fields[$oid] = $data;
//    }

    $rrd_name = $rrd_att_name;
    $rrd_def = $rrd_att_def;
    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'xdsl2LineStatusAttainableRate', $tags, $fieldsAtt);

    $rrd_name = $rrd_act_name;
    $rrd_def = $rrd_act_def;
    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'xdsl2LineStatusActRate', $tags, $fieldsAct);

    //xtuc is CO
    //xtur is CPE (receiver)
    echo 'VDSL (' . $this_port['ifName'] . '/' . Number::formatSi($this_port['xdsl2LineStatusAttainableRateDs'] * $multiplier, 2, 3, 'bps') . '/' . Number::formatSi($this_port['xdsl2LineStatusAttainableRateUs'] * $multiplier, 2, 3, 'bps') . ') \n';
}//end if
