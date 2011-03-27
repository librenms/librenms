<?php

$version = preg_replace("/.+ version (.+) running on .+ (\S+)$/", "\\1||\\2", $sysDescr);
list($version,$hardware) = explode("||", $version);

?>