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

if (stristr($poll_device['sysObjectID'], "packetlogic") || strstr($poll_device['sysObjectID'], "enterprises.15397.2") || strstr($poll_device['sysObjectID'], ".1.3.6.1.4.1.15397.2")) {
    $version = "PacketLogic";
    $hardware = "PacketLogic";
}
