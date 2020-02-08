<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2019 PipoCanaja@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = array (
    'version' => 'telAdminStatusSystemAppVersion.0',
    'hardware'   => 'telAdminStatusSystemBoardType.0',
    'revision'   => 'telAdminStatusSystemBoardRevision.0',
    'serial' => 'telAdminStatusSystemNumSerie.0'
);

$data = snmp_get_multi_oid($device, $oids, '-OUQs', 'TELDAT-MIB');
$data['telAdminStatusSystemBoardType.0'] .= " (rev " . $data['telAdminStatusSystemBoardRevision.0'] . ")";
unset($oids['revision']);

foreach ($oids as $var => $oid) {
    $$var = $data[$oid];
}

unset($data, $oids);
