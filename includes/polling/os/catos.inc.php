<?php

// Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 4.5(9) Copyright (c) 1995-2000 by Cisco Systems, Inc.
// Cisco Systems WS-C5509 Cisco Catalyst Operating System Software, Version 5.5(19) Copyright (c) 1995-2003 by Cisco Systems
// Cisco Systems WS-C5500 Cisco Catalyst Operating System Software, Version 5.5(18) Copyright (c) 1995-2002 by Cisco Systems
// Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 8.4(11)GLX Copyright (c) 1995-2006 by Cisco Systems, Inc.
// Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 5.5(11) Copyright (c) 1995-2001 by Cisco Systems, Inc.
// Cisco Systems, Inc. WS-C4003 Cisco Catalyst Operating System Software, Version 6.4(13) Copyright (c) 1995-2004 by Cisco Systems, Inc.
// Cisco Systems, Inc. WS-C4006 Cisco Catalyst Operating System Software, Version 6.3(9) Copyright (c) 1995-2002 by Cisco Systems, Inc.
if (strstr($ciscomodel, 'OID')) {
    unset($ciscomodel);
}

if (! strstr($ciscomodel, ' ') && strlen($ciscomodel) >= '3') {
    $hardware = $ciscomodel;
}

$device['sysDescr'] = str_replace(', Inc.', '', $device['sysDescr']);
// Make the two formats the same
$device['sysDescr'] = str_replace("\n", ' ', $device['sysDescr']);

[,,$hardware,,,,,,,$version,,,$features] = explode(' ', $device['sysDescr']);
[,$features] = explode('-', $features);
