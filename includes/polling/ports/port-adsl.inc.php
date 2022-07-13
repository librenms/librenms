<?php

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

// Example snmpwalk with units
// "Interval" oids have been filtered out
// adslLineCoding.1 = dmt
// adslLineType.1 = fastOrInterleaved
// adslLineSpecific.1 = zeroDotZero
// adslLineConfProfile.1 = "qwer"
// adslAtucInvSerialNumber.1 = "IES-1000 AAM1008-61"
// adslAtucInvVendorID.1 = "4"
// adslAtucInvVersionNumber.1 = "0"
// adslAtucCurrSnrMgn.1 = 150 tenth dB
// adslAtucCurrAtn.1 = 20 tenth dB
// adslAtucCurrStatus.1 = "00 00 "
// adslAtucCurrOutputPwr.1 = 100 tenth dBm
// adslAtucCurrAttainableRate.1 = 10272000 bps
// adslAturInvVendorID.1 = "0"
// adslAturInvVersionNumber.1 = "0"
// adslAturCurrSnrMgn.1 = 210 tenth dB
// adslAturCurrAtn.1 = 20 tenth dB
// adslAturCurrStatus.1 = "00 00 "
// adslAturCurrOutputPwr.1 = 0 tenth dBm
// adslAturCurrAttainableRate.1 = 1056000 bps
// adslAtucChanInterleaveDelay.1 = 6 milli-seconds
// adslAtucChanCurrTxRate.1 = 8064000 bps
// adslAtucChanPrevTxRate.1 = 0 bps
// adslAturChanInterleaveDelay.1 = 9 milli-seconds
// adslAturChanCurrTxRate.1 = 512000 bps
// adslAturChanPrevTxRate.1 = 0 bps
// adslAtucPerfLofs.1 = 0
// adslAtucPerfLoss.1 = 0
// adslAtucPerfLols.1 = 0
// adslAtucPerfLprs.1 = 0
// adslAtucPerfESs.1 = 0
// adslAtucPerfInits.1 = 1
// adslAtucPerfValidIntervals.1 = 0
// adslAtucPerfInvalidIntervals.1 = 0
// adslAturPerfLoss.1 = 0 seconds
// adslAturPerfESs.1 = 0 seconds
// adslAturPerfValidIntervals.1 = 0
// adslAturPerfInvalidIntervals.1 = 0
if (isset($this_port['adslLineCoding'])) {
    $rrd_name = Rrd::portName($port_id, 'adsl');
    $rrd_def = RrdDefinition::make()->disableNameChecking()
        ->addDataset('AtucCurrSnrMgn', 'GAUGE', 0, 635)
        ->addDataset('AtucCurrAtn', 'GAUGE', 0, 635)
        ->addDataset('AtucCurrOutputPwr', 'GAUGE', 0, 635)
        ->addDataset('AtucCurrAttainableR', 'GAUGE', 0)
        ->addDataset('AtucChanCurrTxRate', 'GAUGE', 0)
        ->addDataset('AturCurrSnrMgn', 'GAUGE', 0, 635)
        ->addDataset('AturCurrAtn', 'GAUGE', 0, 635)
        ->addDataset('AturCurrOutputPwr', 'GAUGE', 0, 635)
        ->addDataset('AturCurrAttainableR', 'GAUGE', 0)
        ->addDataset('AturChanCurrTxRate', 'GAUGE', 0)
        ->addDataset('AtucPerfLofs', 'COUNTER', null, 100000000000)
        ->addDataset('AtucPerfLoss', 'COUNTER', null, 100000000000)
        ->addDataset('AtucPerfLprs', 'COUNTER', null, 100000000000)
        ->addDataset('AtucPerfESs', 'COUNTER', null, 100000000000)
        ->addDataset('AtucPerfInits', 'COUNTER', null, 100000000000)
        ->addDataset('AturPerfLofs', 'COUNTER', null, 100000000000)
        ->addDataset('AturPerfLoss', 'COUNTER', null, 100000000000)
        ->addDataset('AturPerfLprs', 'COUNTER', null, 100000000000)
        ->addDataset('AturPerfESs', 'COUNTER', null, 100000000000)
        ->addDataset('AtucChanCorrectedBl', 'COUNTER', null, 100000000000)
        ->addDataset('AtucChanUncorrectBl', 'COUNTER', null, 100000000000)
        ->addDataset('AturChanCorrectedBl', 'COUNTER', null, 100000000000)
        ->addDataset('AturChanUncorrectBl', 'COUNTER', null, 100000000000);

    $adsl_oids = [
        'AtucCurrSnrMgn',
        'AtucCurrAtn',
        'AtucCurrOutputPwr',
        'AtucCurrAttainableRate',
        'AtucChanCurrTxRate',
        'AturCurrSnrMgn',
        'AturCurrAtn',
        'AturCurrOutputPwr',
        'AturCurrAttainableRate',
        'AturChanCurrTxRate',
        'AtucPerfLofs',
        'AtucPerfLoss',
        'AtucPerfLprs',
        'AtucPerfESs',
        'AtucPerfInits',
        'AturPerfLofs',
        'AturPerfLoss',
        'AturPerfLprs',
        'AturPerfESs',
        'AtucChanCorrectedBlks',
        'AtucChanUncorrectBlks',
        'AturChanCorrectedBlks',
        'AturChanUncorrectBlks',
    ];

    $adsl_db_oids = [
        'adslLineCoding',
        'adslLineType',
        'adslAtucInvVendorID',
        'adslAtucInvVersionNumber',
        'adslAtucCurrSnrMgn',
        'adslAtucCurrAtn',
        'adslAtucCurrOutputPwr',
        'adslAtucCurrAttainableRate',
        'adslAturInvSerialNumber',
        'adslAturInvVendorID',
        'adslAturInvVersionNumber',
        'adslAtucChanCurrTxRate',
        'adslAturChanCurrTxRate',
        'adslAturCurrSnrMgn',
        'adslAturCurrAtn',
        'adslAturCurrOutputPwr',
        'adslAturCurrAttainableRate',
    ];

    $adsl_tenth_oids = [
        'adslAtucCurrSnrMgn',
        'adslAtucCurrAtn',
        'adslAtucCurrOutputPwr',
        'adslAturCurrSnrMgn',
        'adslAturCurrAtn',
        'adslAturCurrOutputPwr',
    ];

    foreach ($adsl_tenth_oids as $oid) {
        $this_port[$oid] = ($this_port[$oid] / 10);
    }

    if (dbFetchCell('SELECT COUNT(*) FROM `ports_adsl` WHERE `port_id` = ?', [$port_id]) == '0') {
        dbInsert(['port_id' => $port_id], 'ports_adsl');
    }

    $port['adsl_update'] = ['port_adsl_updated' => ['NOW()']];
    foreach ($adsl_db_oids as $oid) {
        $data = str_replace('"', '', $this_port[$oid]);
        // FIXME - do we need this?
        $port['adsl_update'][$oid] = $data;
    }

    dbUpdate($port['adsl_update'], 'ports_adsl', '`port_id` = ?', [$port_id]);

    if ($this_port['adslAtucCurrSnrMgn'] > '1280') {
        $this_port['adslAtucCurrSnrMgn'] = 'U';
    }

    if ($this_port['adslAturCurrSnrMgn'] > '1280') {
        $this_port['adslAturCurrSnrMgn'] = 'U';
    }

    $fields = [];
    foreach ($adsl_oids as $oid) {
        $oid = 'adsl' . $oid;
        $data = str_replace('"', '', $this_port[$oid]);
        // Set data to be "unknown" if it's garbled, unexistant or zero
        if (! is_numeric($data)) {
            $data = 'U';
        }
        $fields[$oid] = $data;
    }

    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'adsl', $tags, $fields);

    echo 'ADSL (' . $this_port['adslLineCoding'] . '/' . Number::formatSi($this_port['adslAtucChanCurrTxRate'], 2, 3, 'bps') . '/' . Number::formatSi($this_port['adslAturChanCurrTxRate'], 2, 3, 'bps') . ')';
}//end if
