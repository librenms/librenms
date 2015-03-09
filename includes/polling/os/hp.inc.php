<?php

# Version is the last word in the sysDescr
$version = substr($poll_device['sysDescr'], strrpos($poll_device['sysDescr'], ' ') + 1);

?>
