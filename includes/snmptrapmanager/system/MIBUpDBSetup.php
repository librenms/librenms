<?php

class MIBUpDBSetup {

	const DBSCHEMA = 2;

	public function checkTable($sTableName) {
		$sQuery = 'select 1 from ' . $sTableName  . ' limit 1';
		$aRes = dbQuery($sQuery);

		if ($aRes === false) {
			return false;
		}

		return true;
	}

	public function getDBSchema() {
		$sQuery = 'select value from MIBUploaderPlugin where info like \'dbschema\'';
		$res = dbFetch($sQuery);

		if (count($res) !== 1) {
			throw new MIBUpException('cannot fetch dbschema version: ' . count($res));
		}

		$iDBSchema = (int) $res[0]['value'];
		return $iDBSchema;
	}

	private function updateDBSchema($iToVersion) {
		$sQuery = 'update MIBUploaderPlugin set value = \'' . (int) $iToVersion . '\' where info like \'dbschema\'';
		return $this->executeRaw($sQuery);
	}

	public function resetTables() {
		$sQuery = $this->loadUninstall();
		if($this->executeRaw($sQuery) !== true) {
			throw new MIBUpException('cannot uninstall sql tables');
		}
	}

	private function loadInstall() {
		$sFile = MIBUpUtils::bfp(Array(
			dirname(__FILE__), '..','sql', 'install.sql'
			)
		);

		$res = file_get_contents($sFile);
		return $res;
	}

	private function loadUninstall() {
		$sFile = MIBUpUtils::bfp(Array(
			dirname(__FILE__), '..', 'sql', 'uninstall.sql'
			)
		);
		$res = file_get_contents($sFile);
		return $res;
	}

	private function loadUpgrade($iVersion) {
		$sFile = MIBUpUtils::bfp(Array(
			dirname(__FILE__), '..', 'sql', 'upgradeto.' . $iVersion . '.sql'
			)
		);

		if (is_file($sFile)) {
			$res = file_get_contents($sFile);
			return $res;
		}

		return false;
	}

	private function install() {
		$sQuery = $this->loadInstall();

		$res = $this->executeRaw($sQuery);

		if($res === false) {
			throw new MIBUpException('cannot install');
		}

		return $res;
	}

	private function upgrade() {
		$iDBSchema = $this->getDBSchema() + 1;

		while ($iDBSchema <= self::DBSCHEMA) {
			$upRes = $this->loadUpgrade($iDBSchema);
			if ($upRes === false) {
				throw new MIBUpException('expected upgrade file ' . $iDBSchema);
			}

			$res = $this->executeRaw($upRes);
			if ($res === false) {
				throw new MIBUpException('Cannot upgrade plugin to DBSchema ' . $iDBSchema);
			}

			$this->updateDBSchema($iDBSchema);

			$iDBSchema += 1;
		}
	}

	/**
	 * Executes queries. Insecure.
	 *
	 * You should check for a number of expected $aResults to get the
	 * real state of your SQL commands.
	 *
	 * @param string $sQuery mysql query, already escaped!
	 * @param \Array $aResults an array where to store results
	 * @param \Array $aErrors an array where to store errors
	 */
	public function executeRaw($sQuery, &$aResults = Array(), &$aErrors = Array()) {
		global $database_link;

		$bRes = mysqli_multi_query($database_link, $sQuery);

		// Consume results so we can run other queries after this execution.
		while(true) {
			$oRes = mysqli_store_result($database_link);
			$oErr = mysqli_error($database_link);

			array_push($aResults, $oRes);
			array_push($aErrors, $oErr);

			if(mysqli_more_results($database_link)) {
				mysqli_next_result($database_link);
			} else {
				break;
			}
		}

		return $bRes;
	}

	public function setup($bReset = false) {
		$iSetupSuccess = 0;
		$sErrMsg = '';

		try {
			dbBeginTransaction();

			if ($this->checkTable('MIBUploaderPlugin') && $bReset) {
				$this->resetTables();
			}

			if (!$this->checkTable('MIBUploaderPlugin')) {
				$this->install();
				if (!$this->checkTable('MIBUploaderPlugin')) {
					throw new MIBUpException('installation failed');
				}
			}

			$this->upgrade();

			dbCommitTransaction();
		} catch (MIBUpException $ex) {
			dbRollbackTransaction();
			throw new MIBUpException('cannot setup database properly: ' . $ex->getMessage());
		}
	}
}