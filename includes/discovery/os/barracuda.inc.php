<?php
/*
 * LibreNMS Barracuda OS information module
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!$os) {
    if (stristr($sysDescr, 'Barracuda Load Balancer') || stristr($sysDescr, 'Barracuda Load Balancer ADC')) {
        $os = 'barracudaloadbalancer';
    }
    if (stristr($sysDescr, 'Barracuda Spam Firewall')) {
        $os = 'barracudaspamfirewall';
    }
    if (stristr($sysDescr, 'Barracuda Firewall')) {
        $os = 'barracudangfirewall';
    }

}
