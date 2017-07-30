<?php

class MIBUpCtrlMIBUploadTest extends MIBUpTestCase {

	private $aFiles = null;
	private $oCtrl = null;

	public function setUp() {
		$this->aFiles = Array(
			'name' => Array(
				'MIBNAME1',
				'MIBNAME2'
			),
			'tmpname' => Array(
				'/tmp/MIBNAME1',
				'/tmp/MIBNAME2'
			)
		);

		$this->oCtrl = new MIBUpCtrlMIBUpload();

		$fh = fopen('/tmp/MIBNAME1', 'w');
		fwrite($fh, 'MIB1');
		fclose($fh);

		$fh = fopen('/tmp/MIBNAME2', 'w');
		fwrite($fh, 'MIB2');
		fclose($fh);
	}

	public function test_upload() {
		$this->markTestIncomplete('Use PHPT: https://github.com/Qafoo/blog-examples/blob/master/testing_file_uploads/upload-example.phpt');
		ob_start();
		$this->oCtrl->processUpload($this->aFiles, true);
		ob_end_clean();
	}

	public function tearDown() {
	}

}