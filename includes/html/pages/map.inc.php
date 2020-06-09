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
    print_error('You are using the old style network map, a global map is not available');
} else {
    require_once 'includes/html/print-map.inc.php';
}
