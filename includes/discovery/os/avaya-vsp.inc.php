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

if (starts_with($sysObjectId, array(
    '.1.3.6.1.4.1.2272.202',
    '.1.3.6.1.4.1.2272.203',
    '.1.3.6.1.4.1.2272.205',
    '.1.3.6.1.4.1.2272.206',
    '.1.3.6.1.4.1.2272.208',
    '.1.3.6.1.4.1.2272.209',
    '.1.3.6.1.4.1.2272.210',
))) {
    $os = 'avaya-vsp';
}
