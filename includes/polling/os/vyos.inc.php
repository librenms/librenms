<?php

str_replace("Vyatta", "", $poll_device['sysDescr']);
list(,$version) = explode(" ", $poll_device['sysDescr'], 2);
$features = "";

?>
