<?php

class MIBUpModelInfoTest extends MIBUpTestCase {

	/**
	 * @var MIBUpModelInfo
	 */
	private $oMdl = null;

	public function setUp() {
		$this->oMdl = MIBUpModel::load('Info');
	}

	public function test_getsnmpttstate_idle() {
		$sState = $this->oMdl->getSNMPTTState();
		$this->assertEquals('idle', $sState);
	}

	/**
	 * @depends test_getsnmpttstate_idle
	 */
	public function test_setsnmpttstate_smt() {
		$bQRes = $this->oMdl->setSNMPTTState('something');
		$sState = $this->oMdl->getSNMPTTState();

		$this->assertEquals('something', $sState);
		$this->assertTrue($bQRes);
	}

	/**
	 * @depends test_setsnmpttstate_smt
	 */
	public function test_setsnmpttidle() {
		$bRes = $this->oMdl->setSNMPTTIdle();
		$sState = $this->oMdl->getSNMPTTState();

		$this->assertTrue($bRes);
		$this->assertEquals('idle', $sState);
	}

	public function test_mibstats() {
		$aStats = $this->oMdl->getMIBStats();

		$this->assertGreaterThan(-1, $aStats['mibcount']);
	}

	public function test_dbschemaversion() {
		$iVersion = $this->oMdl->getDBSchemaVersion();

		$this->assertEquals(MIBUpDBSetup::DBSCHEMA, $iVersion);
	}

}