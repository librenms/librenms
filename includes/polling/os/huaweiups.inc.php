<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = ['upsIdentModel.0', 'upsIdentUPSSoftwareVersion.0'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'UPS-MIB');

if (!empty($data[0]['upsIdentModel'])) {
    $hardware = $data[0]['upsIdentModel'];
}
if (!empty($data[0]['upsIdentUPSSoftwareVersion'])) {
    $version = $data[0]['upsIdentUPSSoftwareVersion'];
}
