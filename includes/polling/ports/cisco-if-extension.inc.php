<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */

use LibreNMS\RRD\RrdDefinition;

/*
 * Check if port has one of the counters ('cieIfInRuntsErrs') from CISCO-IF-EXTENSION MIB
 */
if (isset($this_port['cieIfInRuntsErrs'])) {
    /*
     * Build interface RRD with filename in format of:
     * port-id<ifIndex>-cie.rrd
     */
    $rrd_name = Rrd::portName($port_id, 'cie');
    $rrdfile = Rrd::name($device['hostname'], $rrd_name);
    $rrd_def = RrdDefinition::make()
        ->addDataset('InRuntsErrs', 'DERIVE', 0)
        ->addDataset('InGiantsErrs', 'DERIVE', 0)
        ->addDataset('InFramingErrs', 'DERIVE', 0)
        ->addDataset('InOverrunErrs', 'DERIVE', 0)
        ->addDataset('InIgnored', 'DERIVE', 0)
        ->addDataset('InAbortErrs', 'DERIVE', 0)
        ->addDataset('InputQueueDrops', 'DERIVE', 0)
        ->addDataset('OutputQueueDrops', 'DERIVE', 0);

    /*
     * Populate data for RRD
     */
    $rrd_data = [];
    foreach ($cisco_if_extension_oids as $oid) {
        $ds_name = str_replace('cieIf', '', $oid);
        $rrd_data[$ds_name] = $this_port[$oid];
    }

    /*
     * Generate/update RRD
     */
    $ifName = $port['ifName'];
    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'drops', $tags, $rrd_data);
}
