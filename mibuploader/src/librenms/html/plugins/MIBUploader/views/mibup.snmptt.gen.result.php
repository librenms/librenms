<!-- requires mibup.css.img view -->
<?php if(isset($sState)) {
?><h4>SNMPTT is already regenerating/restarting, nothing done.</h4><?php
} else {
?>
<h3>SNMPTT Configuration Generation Results:</h3>
<br />
<h4>SNMPTT Restart Status: </h4>
<?php

foreach($aSyncRess as $sHost => $aSyncRes) {

	if ($aSyncRes[0] != 0) {
		echo ' SYNC FAILED on host ' . $sHost . '<br /><br />Return code: ' . $aSyncRes[0] . '<br /><br />';
		echo 'Output: <br />';
		echo '<pre>' . $aSyncRes[1] . '</pre>';
		echo 'Stderr: <br />';
		echo '<pre>' . $aSyncRes[2] . '</pre>';
	} else {
		if ($aExecRes[$sHost][0] != 0) {
			echo ' FAILED<br /><br />Return code: ' . $aExecRes[$sHost][0] . '<br /><br />';
			echo 'Output: <br />';
			echo '<pre>' . $aExecRes[$sHost][1] . '</pre>';
			echo 'Stderr: <br />';
			echo '<pre>' . $aExecRes[$sHost][2] . '</pre>';
		} else {
			if($aPRSs[$sHost][0] > 0) {
				$sPRS = 'FAILED on ' . $sHost . ': code: ' . $aPRSs[$sHost][0] .
					'<pre>' . $aPRSs[$sHost][1] . '</pre>' .
					'<pre>' . $aPRSs[$sHost][2] . '</pre>';
			} else {
				$sPRS = $sHost . ': OK<br />';
			}
			echo $sPRS;
		}
	}
}
?>
</h4><br />
<ul>
<?php
$sList = '';
foreach($aMessages as $i => $r) {
	$sliclass = 'ok';
	$iTrapsTotal = (int) $aConvertResList[$i][0];
	$iTrapsSuccess = (int) $aConvertResList[$i][1];
	$iTrapsFailed = (int) $aConvertResList[$i][2];

	if ($r[0] != 0 || ($iTrapsTotal != $iTrapsSuccess || $iTrapsFailed > 0)) {
		$sliclass = 'error';
	}

	$sList .= '<li><img class="status ' . $sliclass . '" /> ';

	$sDetails = '';

	switch($r[0]) {
		case 0:
			if ($iTrapsFailed > 0) {
				$sDetails = 'failed: ' . $iTrapsFailed . '/' . $iTrapsTotal;
			} else {
				$sDetails = 'success: ' . $iTrapsSuccess . '/' . $iTrapsTotal;
			}
			break;
		case 1:
			$sDetails = $r[2];
			break;
	}
	$sList .= $r[1] . ': ' . $sDetails . '</li>';
}
echo $sList;
?>
</ul>
<br /><br />
<p>SNMPTT Configuration Generation terminated.</p>
<?php
}