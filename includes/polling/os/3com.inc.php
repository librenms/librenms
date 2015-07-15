<?php

# Version is the last word in the sysDescr's first line
list($version) = explode("\r", substr($poll_device['sysDescr'], strpos($poll_device['sysDescr'], "Release")+8));
 
?>
