<?php

class MIBUpModelSNMPTTTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MIBUpModelSNMPTT
	 */
	private static $oMdl = null;

	public static function setUpBeforeClass() {
		self::$oMdl = MIBUpModel::load('SNMPTT');
	}


	/**
	 * @expectedException MIBUpException
	 */
	public function test_checkinit() {
		self::$oMdl->checkInitialized();
	}

	/**
	 * @depends test_checkinit
	 * @expectedException MIBUpException
	 */
	public function test_setsnmpttconfpath() {
		self::$oMdl->setConfFilePath('/tmp/mibup_test_snmptt.conf');
		self::$oMdl->checkInitialized();
	}

	/**
	 * @depends test_setsnmpttconfpath
	 */
	public function test_setscmb() {
		self::$oMdl->setSNMPTTConvertMIBBin('/tmp/mibup_test_mibconvertbin');
		self::$oMdl->checkInitialized();
	}

	public function test_resetconf() {
		$fh = fopen('/tmp/mibup_test_snmptt.conf', 'w');

		$this->assertTrue(is_resource($fh));

		fwrite($fh, 'bla');
		fclose($fh);

		$sContent = file_get_contents('/tmp/mibup_test_snmptt.conf');

		$this->assertEquals('bla', $sContent);

		self::$oMdl->resetConf();

		$sContent = file_get_contents('/tmp/mibup_test_snmptt.conf');

		$this->assertEquals('', $sContent);
	}
}