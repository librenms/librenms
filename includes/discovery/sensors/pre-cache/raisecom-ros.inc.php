<?php

echo 'rosMgmtOpticalTransceiverDDMTable ';
$pre_cache['rosMgmtOpticalTransceiverDDMTable'] = snmpwalk_cache_twopart_oid($device, 'rosMgmtOpticalTransceiverDDMTable', [], 'ROSMGMT-OPTICAL-TRANSCEIVER-MIB', 'raisecom', ['-OeQUs', '-Ih']);
