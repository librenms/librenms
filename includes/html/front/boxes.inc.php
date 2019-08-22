<?php
/*
 * LibreNMS front page graphs
 *
 * Author: Paul Gear
 * Copyright (c) 2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo '
<div class="cycle-slideshow"
data-cycle-fx="fade"
data-cycle-timeout="10000"
data-cycle-slides="> div"
style="clear: both">
';

foreach (get_matching_files(\LibreNMS\Config::get('html_dir') . '/includes/front/', '/^top_.*\.php$/') as $file) {
    if (($file == 'top_ports.inc.php' && \LibreNMS\Config::get('top_ports') == 0) || ($file == 'top_device_bits.inc.php' && \LibreNMS\Config::get('top_devices') == 0)) {
    } else {
        echo "<div class=box>\n";
        include_once $file;
        echo "</div>\n";
    }
}

echo "</div>\n";
