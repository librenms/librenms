<?php

if (!$os) {
    if (preg_match("/^EdgeOS/", $sysDescr)) {
	$os = "edgeos";
    }
}

?>
