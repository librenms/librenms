<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Johan Zaxmy <johan@zaxmy.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if (str_contains($sysObjectId, '.1.3.6.1.4.1.8691.2.7')) {
    $os = 'moxa-nport';
}
