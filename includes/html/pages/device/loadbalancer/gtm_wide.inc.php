<?php
/*
 * LibreNMS module to display F5 GTM Wide IP Details
 *
 * Adapted from F5 LTM module by Darren Napper
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], ['filter' => ['ignore' => ['=', 0]]]);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the Wide IPs
$keep = [];
$types = [$module, 'f5-gtm-wide'];
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

$subtype = basename($vars['subtype']);
if (is_file("includes/html/pages/device/loadbalancer/$subtype.inc.php")) {
    include "includes/html/pages/device/loadbalancer/$subtype.inc.php";
} else {
    include 'includes/html/pages/device/loadbalancer/gtm_wide_all.inc.php';
}//end if
