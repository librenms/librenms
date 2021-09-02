<?php

use Illuminate\Support\Str;
use LibreNMS\RRD\RrdDefinition;

if (! Str::startsWith($device['os'], ['Snom', 'asa'])) {
    echo ' TCP';
    $oids = [
        'tcpActiveOpens',
        'tcpPassiveOpens',
        'tcpAttemptFails',
        'tcpEstabResets',
        'tcpCurrEstab',
        'tcpInSegs',
        'tcpOutSegs',
        'tcpRetransSegs',
        'tcpInErrs',
        'tcpOutRsts',
    ];
    $data = snmp_getnext_multi($device, $oids, '-OQUs', 'TCP-MIB');

    echo ' TCPHC';
    $hc_oids = [
        'tcpHCInSegs.0',
        'tcpHCOutSegs.0',
    ];
    $hc_data = snmp_getnext_multi($device, $hc_oids, '-OQUs', 'TCP-MIB');

    if ((is_numeric($data['tcpInSegs']) && is_numeric($data['tcpOutSegs'])) || (is_numeric($hc_data['tcpHCInSegs']) && is_numeric($hc_data['tcpHCOutSegs']))) {
        $rrd_def = new RrdDefinition();
        $fields = [];
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 10000000);
            $fields[$oid] = is_numeric($data[$oid]) ? $data[$oid] : 'U';
        }

        // Replace Segs with HC Segs if we have them.
        $fields['tcpInSegs'] = ! empty($hc_data['tcpHCInSegs']) ? $hc_data['tcpHCInSegs'] : $fields['tcpInSegs'];
        $fields['tcpOutSegs'] = ! empty($hc_data['tcpHCOutSegs']) ? $hc_data['tcpHCOutSegs'] : $fields['tcpOutSegs'];

        $tags = compact('rrd_def');
        data_update($device, 'netstats-tcp', $tags, $fields);

        $os->enableGraph('netstat_tcp');

        unset($rrd_def, $fields, $tags, $oid);
    }

    unset($oids, $hc_oids, $data, $hc_data);
}//end if
