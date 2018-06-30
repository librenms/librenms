<?php

// ExtremeXOS version 12.4.1.7 v1241b7 by release-manager on Sat Mar 13 02:36:57 EST 2010
// ExtremeWare XOS version 11.5.2.10 v1152b10 by release-manager on Thu Oct 26 09:53:04 PDT 2006
// ExtremeXOS (X670-48x) version 15.5.2.9 v1552b9-patch1-5 by release-manager on Thu Sep 11 13:03:04 EDT 2014
echo " XOS \n";
list($a,$b,$c,$d,$e,$f,$g,$h,$i,$j,$k,$l,$m,$n) = explode(' ', str_replace('ExtremeWare XOS', 'ExtremeXOS', $device['sysDescr']));
if ($b == 'version') {
    $version  = $c;
    $features = $d.' '.$i.' '.$j.' '.$m;
}
if ($c == 'version') {
    $version  = $d;
    $features = $e.' '.$j.' '.$k.' '.$n;
}


$hardware = rewrite_extreme_hardware($device['sysObjectID']);
if ($hardware == $device['sysObjectID']) {
    unset($hardware);
}

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
