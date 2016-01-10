<?php
if (!$os) {
	if (strstr($sysObjectId, '.1.3.6.1.4.1.890') && preg_match('/^(ES|GS)/', $sysDescr))
		$os = 'zynos';
}
