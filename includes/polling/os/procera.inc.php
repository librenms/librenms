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

if (stristr($poll_device['sysObjectID'], "packetlogic") || strstr($poll_device['sysObjectId'], "enterprises.15397.2")) {
    $version = "PacketLogic";
    $hardware = "PacketLogic";
}
