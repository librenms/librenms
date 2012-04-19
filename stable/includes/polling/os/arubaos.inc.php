<?php

// ArubaOS (MODEL: Aruba3600), Version 6.1.2.2 (29541)
$badchars = array("(", ")", ",");
list(,,$hardware,,$version,) = str_replace($badchars, "", explode (" ", $poll_device['sysDescr']));

?>
