<?php

class MIBUpModelMIB_Empty_Test extends MIBUpTestCase {

	/**
	 * @var MIBUpModelMIB
	 */
	private static $oMdl = null;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$oMdl = MIBUpModel::load('MIB');
	}

	public function test_getid() {
		$res = self::$oMdl->getID('nonexists');
		$this->assertFalse($res);
	}

	/**
	 * @depends test_getid
	 * @expectedException MIBUpException
	 */
	public function test_getcurrentversion() {
		$res = self::$oMdl->getCurrentVersion(0);
	}

	/**
	 * @depends test_getid
	 * @expectedException MIBUpException
	 */
	public function test_getcurrentversion_bq() {
		$res = self::$oMdl->getCurrentVersion(';bla;');
	}

	public function test_getlatestversion() {
		$res = self::$oMdl->getLatestVersion(0);
		$this->assertFalse($res);
	}

	public function test_versionexists() {
		$res = self::$oMdl->versionExists(0, 0);
		$this->assertEquals($res, 0);
	}

	public function test_getversions() {
		$res = self::$oMdl->getVersions(0);
		$this->assertEquals(Array(), $res);
	}

	public function test_getcontent() {
		$res = self::$oMdl->getContent(0);
		$this->assertFalse($res);
	}

	public function test_contentexists() {
		$res = self::$oMdl->contentExists(0, '');
		$this->assertFalse($res);
	}

	public function test_setcurrentversion() {
		$res = self::$oMdl->setCurrentVersion(0, 1);
		$this->assertTrue($res);
	}

	/**
	 * @expectedException MIBUpQueryException
	 */
	public function test_insertcontent() {
		$res = self::$oMdl->insertContent(0, 'content', 1);
	}

	public function test_getall() {
		$res = self::$oMdl->getAll();
		$this->assertEquals(Array(), $res);
	}

	public function test_getname() {
		$res = self::$oMdl->getName(0);
		$this->assertFalse($res);
	}

}