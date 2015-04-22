<?php

$version = preg_replace("/.+ version (.+) running on .+ (\S+)$/", "\\1||\\2", $poll_device['sysDescr']);
list($version,$hardware) = explode("||", $version);

?>