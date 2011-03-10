<?php

$version = preg_replace("/.+ version (.+) running on .+ (\S+)$/", "\||\\2", $sysDescr );
list($version,$hardware) = explode("||", $version);

?>
