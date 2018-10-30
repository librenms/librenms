<?php

// BD6808 - Version 7.8.3 (Build 5) by Release_Master 03/15/10 14:27:35
// Summit48 - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Summit24e3 - Version 6.2e.1 (Build 20) by Release_Master_ABU Tue 05/27/2003 16:46:08
// Summit48 - 1720 Garry - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Summit48(Yonetan) - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Alpine3808 - Version 7.2.0 (Build 33) by Release_Master 07/09/04 14:05:12
echo " Extremeware \n";
list(, $datas) = explode(' - ', $device['sysDescr']);
$datas         = str_replace('(', '', $datas);
$datas         = str_replace(')', '', $datas);
list($a,$b,$c,$d,$e,$f,$g,$h) = explode(' ', $datas);
if ($a == 'Version') {
    $version  = $b;
    $features = $c.' '.$d.' '.$g;
}

$hardware = rewrite_extreme_hardware($device['sysObjectID']);
if ($hardware == $device['sysObjectID']) {
    unset($hardware);
}

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
