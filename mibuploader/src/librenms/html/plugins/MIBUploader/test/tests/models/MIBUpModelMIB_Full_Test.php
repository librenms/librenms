<?php

class MIBUpModelMIB_Full_Test extends MIBUpTestCase {

	/**
	 * @var MIBUpModelMIB
	 */
	private static $oMdl = null;
	private static $iMIBTESTID = null;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$oMdl = MIBUpModel::load('MIB');
	}

	public function test_fullinsert_withup() {
		self::$oMdl->insert('MIBFULL', 'FULLCONTENT');

		$iID = self::$oMdl->getID('MIBFULL');
		$aContent = self::$oMdl->getContent($iID);
		$iCV = self::$oMdl->getCurrentVersion($iID);

		$this->assertEquals(count($aContent), 1);
		$this->assertEquals($aContent[0]['content'], 'FULLCONTENT');
		$this->assertEquals($aContent[1]['version'], 0);
		$this->assertEquals($iCV, 0);
	}

	/**
	 * @depends test_fullinsert_withup
	 */
	public function test_fullinsert_withup2() {
		self::$oMdl->insert('MIBFULL', 'FULLCONTENT2');

		$iID = self::$oMdl->getID('MIBFULL');
		$aContent = self::$oMdl->getContent($iID);
		$iCV = self::$oMdl->getCurrentVersion($iID);

		$this->assertEquals(count($aContent), 2);
		$this->assertEquals($aContent[1]['content'], 'FULLCONTENT2');
		$this->assertEquals($aContent[1]['version'], 1);
		$this->assertEquals($iCV, 1);
	}

	public function test_fullinsert_withoutup() {
		self::$oMdl->insert('MIBFULL', 'FULLCONTENT3', false);

		$iID = self::$oMdl->getID('MIBFULL');
		$aContent = self::$oMdl->getContent($iID);
		$iCV = self::$oMdl->getCurrentVersion($iID);
		$iLV = self::$oMdl->getLatestVersion($iID);

		$this->assertEquals(count($aContent), 3);
		$this->assertEquals($iLV, 2);

		$this->assertEquals($aContent[1]['content'], 'FULLCONTENT2');
		$this->assertEquals($aContent[1]['version'], 1);

		$this->assertEquals($aContent[2]['content'], 'FULLCONTENT3');
		$this->assertEquals($aContent[2]['version'], 2);

		$this->assertEquals($iCV, 1);
	}

	public function test_fullinsert_withoutup2() {
		self::$oMdl->insert('MIBFULL', 'FULLCONTENT4', false);

		$iID = self::$oMdl->getID('MIBFULL');
		$aContent = self::$oMdl->getContent($iID);
		$iCV = self::$oMdl->getCurrentVersion($iID);
		$iLV = self::$oMdl->getLatestVersion($iID);

		$this->assertEquals(count($aContent), 4);
		$this->assertEquals($iLV, 3);

		$this->assertEquals($aContent[1]['content'], 'FULLCONTENT2');
		$this->assertEquals($aContent[1]['version'], 1);

		$this->assertEquals($aContent[3]['content'], 'FULLCONTENT4');
		$this->assertEquals($aContent[3]['version'], 3);

		$this->assertEquals($iCV, 1);
	}
}