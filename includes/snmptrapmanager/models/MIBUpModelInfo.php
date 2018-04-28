<?php

class MIBUpModelInfo extends MIBUpModel {

	public function getSNMPTTState() {
		$sQuery = "select value from MIBUploaderPlugin where info = ?";
		$aParams = Array(
			'snmptt_state'
		);

		$aRes = dbFetch($sQuery, $aParams);

		if (empty($aRes)) {
			return false;
		}

		return $aRes[0]['value'];
	}


	public function setSNMPTTState($sState) {
		$sQuery = "insert into MIBUploaderPlugin values (?, ?) on duplicate key update value=?";
		$aParams = Array(
			'snmptt_state',
			$sState,
			$sState
		);

		return dbQuery($sQuery, $aParams);
	}

	public function setSNMPTTIdle() {
		return $this->setSNMPTTState('idle');
	}

	public function getMIBStats() {
		$sQuery = "select count(*) as count from MIBUploaderMIBS";
		$aRes = dbFetch($sQuery);

		$iMIBCount = (int) $aRes[0]['count'];

		$aStats = Array(
			'mibcount' => $iMIBCount
		);

		return $aStats;
	}

	public function getDBSchemaVersion() {
		$oDBSetup = new MIBUpDBSetup();
		return $oDBSetup->getDBSchema();
	}
}