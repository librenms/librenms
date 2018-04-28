<style>
.trap_list_table td,th {
	min-width: 200px;
	padding-left: 10px;
	padding-right: 10px;
}
</style>

<h3>Received Traps</h3>
<br />
<table class="trap_list_table">
<tr><th>Device</th><th>Trap OID</th><th>Last Update</th></tr>
<?php

foreach($aTraps as $aTrap) {
	$sOid = $aTrap['oid'];
	$iLastUpdate = $aTrap['last_update'];
	$sDevice = $aTrap['hostname'];
	$iDeviceID = $aTrap['device_id'];


	$sDevLink = '<a href=/device/device=' . $iDeviceID . '/>' . $sDevice . '</a>';

	echo '<tr><td>' . $sDevLink . '</td><td>' . $sOid . '</td><td>' . $iLastUpdate . '</td></tr>';
}

?>
</table>