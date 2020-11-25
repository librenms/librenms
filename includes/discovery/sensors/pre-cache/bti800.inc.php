<?php

echo 'BTI800 Tranceiver';

// BTI800
$pre_cache['bti800'] = snmpwalk_cache_multi_oid($device, 'sfpDiagnosticTable', [], 'BTI8xx-SFP-MIB', null, '-OQUbs');
