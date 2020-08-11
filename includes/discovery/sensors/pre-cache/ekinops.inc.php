<?php

$pre_cache['ekishelf'] = snmpwalk_cache_multi_oid($device, 'mgnt2GigmBoardTable', [], 'EKINOPS-MGNT2-MIB', null, '-OQUbs');

$pre_cache['ekicard'] = snmpwalk_cache_multi_oid($device, 'pm20020maMonupRmonBytesCounterClientInputTable', [], 'EKINOPS-Pm20020ma-MIB', null, '-c public16 -OQUbs');
