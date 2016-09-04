<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * Copyright (c) 2016 Daniel Cox <danielcoxman@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.202')) {
        // VSP-4850GTS
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.203')) {
        // VSP-4850GTS-PWR+
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.205')) {
        // VSP-8284XSQ
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.206')) {
        // VSP-4450GSX-PWR+
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.208')) {
        // VSP-8404
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.209')) {
        // VSP-7254XSQ
        $os = 'avaya-vsp';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2272.210')) {
        // VSP-7254XTQ
        $os = 'avaya-vsp';
    }
}
