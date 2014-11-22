<?php

if (!$os || $os == "vyatta") {
    if (preg_match("/^Vyatta VyOS/", $sysDescr) || preg_match("/^VyOS/", $sysDescr)) {
	$os = "vyos";
    }
}

?>
