<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$pagetitle[] = 'Map';

if (\LibreNMS\Config::get('gui.network-map.style') == 'old') {
    echo '
<center style="height:100%">
    <object data="network-map.php?device=' . $device['device_id'] . '&format=svg" type="image/svg+xml" style="width: 100%; height:100%"></object>
</center>
    ';
} else {
    require_once 'includes/html/print-map.inc.php';
}
