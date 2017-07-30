<?php

class MIBUpDBSetupTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MIBUpDBSetup
	 */
	private $oDBSetup = null;

	public function setUp() {
		$this->oDBSetup = new MIBUpDBSetup();
	}

	public function test_executeRaw_ok() {
		$aResults = Array();
		$aErrors = Array();

		$bRes = $this->oDBSetup->executeRaw('select 1; select 2;', $aResults, $aErrors);

		foreach($aResults as $i => $oResult) {
			$aRes = mysqli_fetch_assoc($oResult);
			$this->assertEquals($aRes[$i + 1], $i + 1);
		}

		foreach($aErrors as $oError) {
			$this->assertEmpty($oError);
		}

		$this->assertTrue($bRes);
	}

	/**
	 * @depends test_executeRaw_ok
	 */
	public function test_executeRaw_nok() {
		$aResults = Array();
		$aErrors = Array();
		$sBadQuery = "select 1; nawak1; select 2; nawak2;";

		$bRes = $this->oDBSetup->executeRaw($sBadQuery, $aResults, $aErrors);

		$this->assertTrue($bRes);
		$this->assertEquals(count($aResults), 2);
		$this->assertEquals(count($aErrors), 2);
	}

	/**
	 * @depends test_executeRaw_nok
	 */
	public function test_checkTable() {
		dbBeginTransaction();
		$this->oDBSetup->executeRaw('create table MIBUploader_phpunit_test (id INT(11));');
		$bRes = $this->oDBSetup->checkTable('MIBUploader_phpunit_test');
		$this->assertTrue($bRes);

		$this->oDBSetup->executeRaw('drop table MIBUploader_phpunit_test;');
		$bRes = $this->oDBSetup->checkTable('MIBUploader_phpunit_test');
		$this->assertFalse($bRes);
		dbRollbackTransaction();
	}

	/**
	 * @depends test_checkTable
	 */
	public function test_install() {
		$this->oDBSetup->setup();

		$this->assertTrue($this->oDBSetup->checkTable('MIBUploaderPlugin'));
	}

	/**
	 * @depends test_install
	 */
	public function test_dbschema() {
		$iRes = $this->oDBSetup->getDBSchema();
		$this->assertGreaterThan(0, $iRes);
		$this->assertInternalType('int', $iRes);
	}

	/**
	 * @depends test_dbschema
	 */
	public function test_uninstall() {
		$this->oDBSetup->resetTables();

		$this->assertFalse($this->oDBSetup->checkTable('MIBUploaderPlugin'));
	}

	/**
	 * @depends test_uninstall
	 * @expectedException MIBUpException
	 */
	public function test_dbschema_uninstalled() {
		$iRes = $this->oDBSetup->getDBSchema();
	}

}