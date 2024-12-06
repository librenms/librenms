<?php

echo 'raisecomOpticalTransceiverDDMTable ';
$pre_cache['raisecomOpticalTransceiverDDMTable'] = snmpwalk_cache_twopart_oid($device, 'raisecomOpticalTransceiverDDMTable', [], 'RAISECOM-OPTICAL-TRANSCEIVER-MIB', 'raisecom', ['-OeQUs', '-Pu']);
