<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == "cisco") {

    /*
     * Cisco WWAN
     * This module graphs the MNC and RSSI values for a Cisco Wireless
     * WAN router
     */
     include 'wireless/cisco-wwan.inc.php';

}
