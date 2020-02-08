<?php

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
