<?php
/**
 * SPLAT.inc.php
 *
 * NTTCOM MS poller module for Check Point SECUREPLATFORM
**/

use LibreNMS\RRD\RrdDefinition;

$tmp_splat = snmp_get_multi_oid($device, ['svnVersion.0', 'svnApplianceProductName.0', 'svnApplianceSerialNumber.0'], '-OUQs', 'CHECKPOINT-MIB');
$serial   = $tmp_splat['svnApplianceSerialNumber.0'];
$hardware = $tmp_splat['svnApplianceProductName.0'];
$version  = $tmp_splat['svnVersion.0'];
unset($tmp_SPLAT);

$connections = snmp_get($device, 'fwNumConn.0', '-OQv', 'CHECKPOINT-MIB');

if (is_numeric($connections)) {
        $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

        $fields = array(
                'NumConn' => $connections,
        );

        $tags = compact('rrd_def');
        data_update($device, 'secureplatform_sessions', $tags, $fields);
        $graphs['secureplatform_sessions'] = true;
}

unset($connections);
