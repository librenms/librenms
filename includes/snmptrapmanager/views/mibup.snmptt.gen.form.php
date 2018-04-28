<h3>SNMPTT Configuration Generation</h3>

<form method="post" action="/snmptrapmanager/?mode=snmptt" style="margin-left: 20px;">
<p>Don't select any MIB to regenerate everything. Beware that generating only selected MIBs will flush any other already generated snmptt configuration.</p><br />
<input type="submit" name="snmptt_mib_gen_submit" />
<input type="reset" />
<label><input type="checkbox" name="snmptt_restart_only" /> Only restart SNMPTT</label>
<br /><br />
<?php
$bFlip = false;
foreach($aMIBList as $aMIB) {
	if ($bFlip) {
		$sColor = 'lightgrey';
	} else {
		$sColor = 'white';
	}
	$bFlip = !$bFlip;
	$sCBVal = $aMIB['id'];
	echo '<div style="display: inline-block; background-color: '.$sColor.'">';
	echo '<label><div style="display: inline-block; text-align: right; padding-right: 20px; min-width: 300px;">' . $aMIB['name'] . ' </div><input type="checkbox" name="snmptt_mib_gen_ids[]" value="' . $sCBVal . '" /></label><br />';
	echo '</div><br />';
}
?>
<br />
<input type="submit" name="snmptt_mib_gen_submit" />
<input type="reset" />
</form>
