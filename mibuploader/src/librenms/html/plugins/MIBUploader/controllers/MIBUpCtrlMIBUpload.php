<?php

class MIBUpCtrlMIBUpload extends MIBUpCtrl {

	private $oMIB = null;

	public function __construct() {
		parent::__construct();
		$this->oMIB = $this->loadModel('MIB');
	}

	public function run() {
		if(isset($_FILES['form_upload_mib_mibs'])) {

			$aFiles = $_FILES['form_upload_mib_mibs'];
			$bUpdateVersion = false;

			if (isset($_POST['form_upload_mib_update_version'])) {
				$bUpdateVersion = true;
			}

			$this->processUpload($aFiles, $bUpdateVersion);

		} else {
			$this->processForm();
		}
	}

	private function processForm() {
		echo $this->loadView('mibup.form.upload.mib')->render();
	}

	/**
	 * Take the uploaded file written on server's disk, then
	 * try to insert in DB.
	 *
	 * Process the whole upload & insert & view result steps.
	 *
	 * @param Array $aFiles set as $_FILES is set
	 * @param bool $bUpdateVersion update to the latest version if it's an update
	 */
	public function processUpload($aFiles, $bUpdateVersion) {
		$aUpResults = Array();
		$iUpMIBS = count($aFiles['name']);

		for($iUMIdx = 0; $iUMIdx < $iUpMIBS; $iUMIdx++) {
			$sUpFileName = basename($aFiles['name'][$iUMIdx]);
			$sUpFileTMP = $aFiles['tmp_name'][$iUMIdx];
			$aMIBInfos = $this->uploadMIB($sUpFileName, $sUpFileTMP);

			if(!is_array($aMIBInfos)) {
				array_push($aUpResults, Array(1, $sUpFileName));
			} else {
				$sMIBName = $aMIBInfos[0];
				$sMIBContent = file_get_contents($aMIBInfos[1]);
				$res = $this->insertMIB($sMIBName, $sMIBContent, $bUpdateVersion);

				if($res !== true) {
					array_push($aUpResults, Array(2, $sUpFileName, $res));
				} else {
					array_push($aUpResults, Array(0, $sUpFileName));
				}
			}
		}

		echo $this->loadView('mibup.css.img')->render();
		echo $this->loadView('mibup.process.upload.mib')
			->set('aUpResults', $aUpResults)
			->render();
	}

	/**
	 * Get upload files and store them on disk for further use.
	 */
	private function uploadMIB($sUpFileName, $sUpFileTMP) {
		$sUpFileName = basename($sUpFileName);

		$sUpFileDest = MIBUpUtils::getConf('upload_dir') .
			DIRECTORY_SEPARATOR . $sUpFileName;

		if(is_uploaded_file($sUpFileTMP)) {
			$res = move_uploaded_file($sUpFileTMP, $sUpFileDest);
			if(!$res) {
				return 2;
			} else {
				return Array($sUpFileName, $sUpFileDest);
			}
		} else {
			return 1;
		}
	}

	/**
	 * Insert a previously uploaded MIB file into database.
	 */
	private function insertMIB($sName, $sContent, $bUpdateVersion) {
		$aRes = $this->oMIB->contentExists($sName, $sContent);

		if (is_array($aRes) && count($aRes) > 0) {
			return 'already uploaded with version ' . $aRes[0]['version'];
		}

		try {
			$this->oMIB->insert($sName, $sContent, $bUpdateVersion);
		} catch (MIBUpException $ex) {
			return $ex->getMessage();
		}

		return true;
	}
}