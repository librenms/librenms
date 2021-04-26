<?php
/*
 * LibreNMS module to Graph Cisco Voice components.
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == 'cisco') {
    /*
     * Cisco PRI
     * This module graphs the used and total DS0 channels on a Cisco Voice Gateway
     */
    include 'cisco-voice/cisco-iospri.inc.php';

    /*
     * Cisco IP
     * This module graphs the used IP channels on a Cisco Voice Gateway
     */
    include 'cisco-voice/cisco-ip.inc.php';

    /*
     * Cisco DSP
     * This module graphs the used and total DSP resources on a Cisco Voice Gateway
     */
    include 'cisco-voice/cisco-iosdsp.inc.php';

    /*
     * Cisco MTP
     * This module graphs the used and total MTP resources on a Cisco Voice Gateway
     */
    include 'cisco-voice/cisco-iosmtp.inc.php';

    /*
     * Cisco XCode
     * This module graphs the used and total Transcoder resources on a Cisco Voice Gateway
     */
    include 'cisco-voice/cisco-iosxcode.inc.php';
}

unset(
    $output,
    $key
);
