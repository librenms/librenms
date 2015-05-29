<?php

if (!$os) {
    if (preg_match("/^Pacific Broadband Networks/", $sysDescr)) {
	$os = "pbn";
    }
}

?>
