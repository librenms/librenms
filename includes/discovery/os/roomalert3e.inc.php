<?php

if (!$os && ($sysObjectId == '.1.3.6.1.4.1.20916' || strpos($sysObjectId, '.1.3.6.1.4.1.20916.') === 0)) {
	if (strpos($sysDescr, "Room Alert") !== FALSE) { $os = "roomalert3e"; }
}
