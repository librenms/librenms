<?php

#list($hardware, $features, $version) = explode(",", str_replace(", ", ",", $sysDescr));
#list($version) = explode("(", $version);

preg_match("/MN:(AP\d+) /",$sysDescr,$matches);
$hardware = str_replace('MN:','',$matches[0]);

?>
