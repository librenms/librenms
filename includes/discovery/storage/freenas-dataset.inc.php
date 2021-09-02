<?php

$datasetTable_array = snmpwalk_cache_oid($device, 'datasetTable', null, 'FREENAS-MIB');

$sql = "SELECT `storage_descr` FROM `storage` WHERE `device_id`  = '" . $device['device_id'] . "' AND `storage_type` != 'dataset'";
$tmp_storage = dbFetchColumn($sql);

if (is_array($datasetTable_array)) {
    foreach ($datasetTable_array as $index => $dataset) {
        if (isset($dataset['datasetDescr'])) {
            if (! in_array($dataset['datasetDescr'], $tmp_storage)) {
                $dataset['datasetIndex'] = $index;
                $dataset['datasetTotal'] = $dataset['datasetSize'] * $dataset['datasetAllocationUnits'];
                $dataset['datasetAvail'] = ($dataset['datasetAvailable'] * $dataset['datasetAllocationUnits']);
                $dataset['datasetUsed'] = $dataset['datasetTotal'] - $dataset['datasetAvail'];

                discover_storage($valid_storage, $device, $dataset['datasetIndex'], 'dataset', 'freenas-dataset', $dataset['datasetDescr'], $dataset['datasetTotal'], $dataset['datasetAllocationUnits'], $dataset['datasetUsed']);
            }
        }
    }
}
