<?php

class MIBUpModelMIB_Regular_Test extends MIBUpTestCase {

	/**
	 * @var MIBUpModelMIB
	 */
	private static $oMdl = null;
	private static $iMIBTESTID = null;
	private static $iMIBTESTID2 = null;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$oMdl = MIBUpModel::load('MIB');
	}

	public function test_create_mibentry_getid() {
		$res = self::$oMdl->createMIBEntry('MIBTEST');
		$this->assertTrue($res);

		$iID = self::$oMdl->getID('MIBTEST');
		$this->assertInternalType('int', $iID);

		self::$iMIBTESTID = $iID;


		$res2 = self::$oMdl->createMIBEntry('MIBPOLUTE');
		$this->assertTrue($res2);

		$iID2 = self::$oMdl->getID('MIBPOLUTE');
		$this->assertInternalType('int', $iID2);
		$this->assertTrue($iID != $iID2);

		self::$iMIBTESTID2 = $iID2;
	}

	/**
	 * @depends test_create_mibentry_getid
	 */
	public function test_insertcontent() {
		$res = self::$oMdl->insertContent(self::$iMIBTESTID, 'MIBINSERT', 0);
		$this->assertTrue($res);

		$res2 = self::$oMdl->insertContent(self::$iMIBTESTID2, 'MIBINSERT', 0);
		$this->assertTrue($res2);
	}

	/**
	 * @depends test_insertcontent
	 */
	public function test_getcontent_inserted() {
		$aContent = self::$oMdl->getContent(self::$iMIBTESTID, 0);

		$this->assertEquals('MIBINSERT', $aContent['content']);
		$this->assertEquals('0', $aContent['version']);
		$this->assertEquals('1', $aContent['id_mib']);
	}

	/**
	 * @depends test_getcontent_inserted
	 */
	public function test_versionexists() {
		$res = self::$oMdl->versionExists(self::$iMIBTESTID, 0);
		$this->assertEquals($res, 1);
	}

	/**
	 * @depends test_versionexists
	 */
	public function test_updatecontent() {
		$res = self::$oMdl->insertContent(self::$iMIBTESTID, 'MIBUPDATE', 0);
		$this->assertTrue($res);
	}

	/**
	 * @depends test_updatecontent
	 */
	public function test_getcontent_updated() {
		$aContent = self::$oMdl->getContent(self::$iMIBTESTID, 0);

		$this->assertEquals('MIBUPDATE', $aContent['content']);
		$this->assertEquals('0', $aContent['version']);
		$this->assertEquals('1', $aContent['id_mib']);
	}

	/**
	 * @depends test_getcontent_updated
	 */
	public function test_getcurrentversion() {
		$iCV = self::$oMdl->getCurrentVersion(self::$iMIBTESTID);
		$this->assertEquals($iCV, 0);
	}

	/**
	 * @depends test_getcurrentversion
	 */
	public function test_insertnewcontent() {
		$res = self::$oMdl->insertContent(self::$iMIBTESTID, 'MIBINSERTNEW', 1);
		$this->assertTrue($res);

		$aContent = self::$oMdl->getContent(self::$iMIBTESTID);

		$this->assertEquals(count($aContent), 2);
		$this->assertEquals($aContent[0]['version'], 0);
		$this->assertEquals($aContent[0]['content'], 'MIBUPDATE');
		$this->assertEquals($aContent[1]['version'], 1);
		$this->assertEquals($aContent[1]['content'], 'MIBINSERTNEW');
	}

	/**
	 * @depends test_insertnewcontent
	 */
	public function test_getversions() {
		$res = self::$oMdl->getVersions(self::$iMIBTESTID);
		$this->assertEquals(count($res), 2);
		$this->assertEquals($res[0]['version'], 0);
		$this->assertEquals($res[1]['version'], 1);
	}

	/**
	 * @depends test_insertnewcontent
	 */
	public function test_contentexists_withoutv() {
		$res = self::$oMdl->contentExists('MIBTEST', 'MIBUPDATE');

		$this->assertEquals($res[0]['id_mib'], self::$iMIBTESTID);
		$this->assertEquals($res[0]['version'], 0);
		$this->assertEquals($res[0]['content'], 'MIBUPDATE');
	}

	/**
	 * @depends test_insertnewcontent
	 */
	public function test_getlatestversion() {
		$res = self::$oMdl->getLatestVersion(self::$iMIBTESTID);
		$this->assertEquals($res, 1);
	}

	/**
	 * @depends test_getlatestversion
	 */
	public function test_setcurrentversion() {
		$res = self::$oMdl->setCurrentVersion(self::$iMIBTESTID, 1);

		$this->assertTrue($res);

		$iCV = self::$oMdl->getCurrentVersion(self::$iMIBTESTID);

		$this->assertEquals($iCV, 1);
	}
}