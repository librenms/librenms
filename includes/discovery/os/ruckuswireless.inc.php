<?php
/*
 * LibreNMS Ruckus Wireless OS information module
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.25053.3.1')) {
        $os = 'ruckuswireless';

        $ruckus_mibs = array(
            "ruckusZDSystemStats"           => "RUCKUS-ZD-SYSTEM-MIB",
            "ruckusZDWLANTable"             => "RUCKUS-ZD-WLAN-MIB",
            "ruckusZDWLANAPTable"           => "RUCKUS-ZD-WLAN-MIB",
            "ruckusZDWLANAPRadioStatsTable" => "RUCKUS-ZD-WLAN-MIB",
        );
        register_mibs($device, $ruckus_mibs, "includes/discovery/os/ruckuswireless.inc.php");
    }
}
