<?php
/**
 * SPLAT.inc.php
 *
 * NTTCOM MS poller module for Check Point SECUREPLATFORM
**/

use LibreNMS\RRD\RrdDefinition;

$tmp_SPLAT = snmp_get_multi_oid($device, 'svnVersion.0 svnApplianceProductName.0 svnApplianceSerialNumber.0', '-OUQs', 'CHECKPOINT-MIB');
$serial   = $tmp_SPLAT['svnApplianceSerialNumber.0'];
$hardware = $tmp_SPLAT['svnApplianceProductName.0'];
$version  = $tmp_SPLAT['svnVersion.0'];
unset($tmp_SPLAT);

$connections = snmp_get($device, 'fwNumConn.0', '-OQv', 'CHECKPOINT-MIB');

if (is_numeric($connections)) {
        $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

        $fields = array(
                'NumConn' => $connections,
        );

        $tags = compact('rrd_def');
        data_update($device, 'splat_actsessions', $tags, $fields);
        $graphs['splat_actsessions'] = true;
}

unset($connections);
