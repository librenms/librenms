<?php
/*
 * LibreNMS module to capture details from various Load Balancers
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'f5') {
    include 'includes/polling/loadbalancers/f5-ltm.inc.php';
    include 'includes/polling/loadbalancers/f5-gtm.inc.php';
}
