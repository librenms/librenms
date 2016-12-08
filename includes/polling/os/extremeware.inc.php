<?php

echo 'Doing Extreme: ';

// BD6808 - Version 7.8.3 (Build 5) by Release_Master 03/15/10 14:27:35
// Summit48 - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Summit24e3 - Version 6.2e.1 (Build 20) by Release_Master_ABU Tue 05/27/2003 16:46:08
// Summit48 - 1720 Garry - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Summit48(Yonetan) - Version 4.1.19 (Build 2) by Release_Master  Wed 08/09/2000  6:09p
// Alpine3808 - Version 7.2.0 (Build 33) by Release_Master 07/09/04 14:05:12
if (!strpos($poll_device['sysDescr'], 'XOS')) {
    echo " Extremeware \n";
    list(, $datas) = explode(' - ', $poll_device['sysDescr']);
    $datas         = str_replace('(', '', $datas);
    $datas         = str_replace(')', '', $datas);
    list($a,$b,$c,$d,$e,$f,$g,$h) = explode(' ', $datas);
    if ($a == 'Version') {
        $version  = $b;
        $features = $c.' '.$d.' '.$g;
    }
} else {
    // ExtremeXOS version 12.4.1.7 v1241b7 by release-manager on Sat Mar 13 02:36:57 EST 2010
    // ExtremeWare XOS version 11.5.2.10 v1152b10 by release-manager on Thu Oct 26 09:53:04 PDT 2006
    // ExtremeXOS (X670-48x) version 15.5.2.9 v1552b9-patch1-5 by release-manager on Thu Sep 11 13:03:04 EDT 2014
    echo " XOS \n";
    list($a,$b,$c,$d,$e,$f,$g,$h,$i,$j,$k,$l,$m,$n) = explode(' ', str_replace('ExtremeWare XOS', 'ExtremeXOS', $poll_device['sysDescr']));
    if ($b == 'version') {
        $version  = $c;
        $features = $d.' '.$i.' '.$j.' '.$m;
    }
    if ($c == 'version') {
        $version  = $d;
        $features = $e.' '.$j.' '.$k.' '.$n;
    }
}

$hardware = rewrite_extreme_hardware($poll_device['sysObjectID']);
if ($hardware == $poll_device['sysObjectID']) {
    unset($hardware);
}

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
