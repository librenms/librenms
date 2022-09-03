<?php

use App\Models\PortVdsl;
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
        'xdsl2LineStatusActAtpDs',
        'xdsl2LineStatusActAtpUs',
    ];
    $vdsl_tenth_oids = [
        'xdsl2LineStatusActAtpDs',
        'xdsl2LineStatusActAtpUs',
    ];

    foreach ($vdsl_tenth_oids as $oid) {
        $this_port[$oid] = ($this_port[$oid] / 10);
    }

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

    $rrd_pow_name = Rrd::portName($port_id, 'xdsl2LineStatusActAtp');
    $rrd_pow_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('ds', 'GAUGE', 0)
        ->addDataset('us', 'GAUGE', 0);
    // xdsl2LineStatusActAtpDs.11 = 142  (equivalent adslAturCurrOutputPwr.13 = 142)
    // xdsl2LineStatusActAtpUs.11 = 67 (equivlent adslAtucCurrOutputPwr.13 = 67)

    $port['vdsl_update'] = ['port_vdsl_updated' => ['NOW()']];
    $port['vdsl_update']['xdsl2ChStatusActDataRateXtur'] = $this_port['xtur']['xdsl2ChStatusActDataRate'] ?? 0;
    $port['vdsl_update']['xdsl2ChStatusActDataRateXtuc'] = $this_port['xtuc']['xdsl2ChStatusActDataRate'] ?? 0;
    foreach ($vdsl_db_oids as $oid) {
        if (isset($this_port[$oid])) {
            $port['vdsl_update'][$oid] = $this_port[$oid];
        }
    }

    PortVdsl::updateOrCreate(['port_id' => $port_id], $port['vdsl_update']);

    $fieldsAtt['ds'] = $this_port['xdsl2LineStatusAttainableRateDs'];
    $fieldsAtt['us'] = $this_port['xdsl2LineStatusAttainableRateUs'];

    $fieldsPow['ds'] = $this_port['xdsl2LineStatusActAtpDs'];
    $fieldsPow['us'] = $this_port['xdsl2LineStatusActAtpUs'];

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

    $rrd_name = $rrd_pow_name;
    $rrd_def = $rrd_pow_def;
    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'xdsl2LineStatusActAtp', $tags, $fieldsPow);

    //xtuc is CO
    //xtur is CPE (receiver)
    echo 'VDSL (' . $this_port['ifName'] . '/' . Number::formatSi($this_port['xdsl2LineStatusAttainableRateDs'] * $multiplier, 2, 3, 'bps') . '/' . Number::formatSi($this_port['xdsl2LineStatusAttainableRateUs'] * $multiplier, 2, 3, 'bps') . ') \n';
}//end if
